<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderAssignment;
use App\Models\Technician;
use App\Services\BalanceService;
use App\Services\NotificationService;
use App\Services\SurveyBalanceService;
use Illuminate\Http\Request;
use App\Mail\OrderConfirmedMail;
use App\Mail\WarrantyActiveMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class AssignmentController extends Controller
{
    public function __construct(
        private BalanceService $balanceService,
        private NotificationService $notificationService,
        private SurveyBalanceService $surveyBalanceService,
    ) {}

    public function pendingOrders(Request $request)
    {
        $user = $request->user();
        $bp   = $user->businessPartner;
        abort_if(!$bp, 403, 'Bukan akun Business Partner.');

        $orders = Order::with(['items.bpService.serviceType', 'address', 'phone'])
            ->where('bp_id', $bp->id)
            ->where('status', 'confirmed')
            ->whereNull('technician_id')
            ->where('payment_status', 'paid')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($o) => $this->formatOrder($o));

        return response()->json(['orders' => $orders]);
    }

    public function myTechnicians(Request $request)
    {
        $user = $request->user();
        $bp   = $user->businessPartner;
        abort_if(!$bp, 403, 'Bukan akun Business Partner.');

        $technicians = Technician::with('user')
            ->where('bp_id', $bp->id)
            ->where('status', 'approved')
            ->get()
            ->map(fn($t) => [
                'id'        => $t->id,
                'name'      => $t->user->name,
                'grade'     => $t->grade,
                'balance'   => (float) $t->balance,
                'city'      => $t->city,
                'districts' => $t->districts,
            ]);

        return response()->json(['technicians' => $technicians]);
    }

    public function assign(Request $request)
    {
        $validated = $request->validate([
            'order_id'      => 'required|exists:orders,id',
            'technician_id' => 'required|exists:technicians,id',
            'notes'         => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $bp   = $user->businessPartner;
        abort_if(!$bp, 403, 'Bukan akun Business Partner.');

        $order = Order::with(['address', 'user'])
            ->where('id', $validated['order_id'])
            ->where('bp_id', $bp->id)
            ->firstOrFail();

        abort_if($order->technician_id, 422, 'Order sudah di-assign ke teknisi.');
        abort_if($order->status !== 'confirmed', 422, 'Order belum siap di-assign.');
        abort_if($order->payment_status !== 'paid', 422, 'Order belum dibayar.');

        $technician = Technician::with('user')
            ->where('id', $validated['technician_id'])
            ->where('bp_id', $bp->id)
            ->where('status', 'approved')
            ->firstOrFail();

        DB::transaction(function () use ($order, $technician, $user, $validated) {
            // Order perbaikan fase survey → status survey_in_progress
            $nextStatus = ($order->is_perbaikan && $order->perbaikan_phase === 'survey')
                ? 'survey_in_progress'
                : 'in_progress';

            $order->update([
                'technician_id' => $technician->id,
                'status'        => $nextStatus,
            ]);

            OrderAssignment::create([
                'order_id'      => $order->id,
                'technician_id' => $technician->id,
                'assigned_by'   => $user->id,
                'status'        => 'assigned',
                'notes'         => $validated['notes'] ?? null,
            ]);
        });

        // Notif teknisi
        if ($technician->user->fcm_token) {
            $this->notificationService->notifyTechnicianAssigned(
                $technician->user->fcm_token,
                $order->id,
                $order->address->city_name ?? '-'
            );
        }

        // Notif customer
        if ($order->user->fcm_token) {
            $this->notificationService->notifyOrderConfirmed(
                $order->user->fcm_token,
                $order->id
            );
        }

        Mail::to($order->user->email)->queue(new OrderConfirmedMail($order));

        return response()->json(['message' => 'Teknisi berhasil di-assign.']);
    }

    // Dipanggil dari app teknisi
    public function technicianComplete(Request $request, Order $order)
    {
        $user       = $request->user();
        $technician = Technician::where('user_id', $user->id)->first();

        abort_if(!$technician, 403, 'Bukan akun teknisi.');
        abort_if($order->technician_id !== $technician->id, 403, 'Bukan order kamu.');
        abort_if($order->status !== 'in_progress', 422, 'Order tidak dalam status in_progress.');

        $autoCompleteAt = now()->addMinutes(30);

        $order->update([
            'status'           => 'waiting_confirmation',
            'auto_complete_at' => $autoCompleteAt,
        ]);

        OrderAssignment::where('order_id', $order->id)
            ->where('status', 'assigned')
            ->update(['status' => 'completed', 'completed_at' => now()]);

        if ($order->user->fcm_token) {
            $this->notificationService->notifyWaitingConfirmation(
                $order->user->fcm_token,
                $order->id
            );
        }

        return response()->json([
            'message'          => 'Order ditandai selesai. Menunggu konfirmasi customer.',
            'auto_complete_at' => $autoCompleteAt->toIso8601String(),
        ]);
    }

    // Dipanggil dari app customer — klik "Selesai"
    public function customerConfirm(Request $request, Order $order)
    {
        abort_if($order->user_id !== $request->user()->id, 403, 'Bukan order kamu.');
        abort_if($order->status !== 'waiting_confirmation', 422, 'Order tidak menunggu konfirmasi.');

        DB::transaction(function () use ($order) {

            // ─── Order perbaikan fase 2 ───────────────────────────
            // Balance cair gabungan (survey + fase2), garansi aktif
            if ($order->is_perbaikan && $order->perbaikan_phase === 'phase2') {
                $order->update([
                    'status'           => 'completed',
                    'auto_complete_at' => null,
                ]);

                // SurveyBalanceService handle:
                // - Hitung total (survey + fase2)
                // - Release ke teknisi, BP, ACD
                // - Set garansi di fase2Order
                $this->surveyBalanceService->release($order);
                return;
            }

            // ─── Order biasa (non-perbaikan) ─────────────────────
            $order->update([
                'status'              => 'warranty',
                'warranty_started_at' => now(),
                'warranty_expires_at' => now()->addDays(7),
                'auto_complete_at'    => null,
            ]);

            if ($order->order_type === 'relokasi') {
                $this->balanceService->distributeRelocationEarning($order);
            } else {
                $this->balanceService->distributeOrderEarning($order);
            }
        });

        // ─── Notif setelah transaction ────────────────────────────

        // Perbaikan fase 2 — notif teknisi saldo masuk sudah di-handle SurveyBalanceService
        // Hanya kirim notif customer garansi aktif jika order biasa
        if (!($order->is_perbaikan && $order->perbaikan_phase === 'phase2')) {
            $technician = $order->technician;
            if ($technician?->user->fcm_token) {
                $grade    = $technician->grade ?? 'beginner';
                $rates    = BalanceService::GRADE_RATES[$grade];
                $order->load('items.bpService.serviceType');

                $bongkarTotal = (float) $order->items
                    ->filter(fn($i) => $i->bpService?->serviceType?->category === 'relokasi_bongkar')
                    ->sum('subtotal');
                $pasangTotal = (float) $order->items
                    ->filter(fn($i) => $i->bpService?->serviceType?->category === 'relokasi_pasang')
                    ->sum('subtotal');

                if ($bongkarTotal == 0 && $pasangTotal == 0) {
                    $half         = round((float) $order->total_amount / 2, 2);
                    $bongkarTotal = $half;
                    $pasangTotal  = $half;
                }

                $base      = $order->order_type === 'relokasi' ? $bongkarTotal : (float) $order->total_amount;
                $techShare = round($base * $rates['technician'] / 100, 2);

                $this->notificationService->notifyBalanceReleased(
                    $technician->user->fcm_token,
                    $techShare,
                    $order->id
                );
            }

            // Notif teknisi pasang (relokasi beda lokasi)
            if ($order->split_technician && $order->second_technician_id) {
                $techPasang = Technician::with('user')->find($order->second_technician_id);
                if ($techPasang?->user->fcm_token) {
                    $grade     = $techPasang->grade ?? 'beginner';
                    $rates     = BalanceService::GRADE_RATES[$grade];
                    $techShare = round($pasangTotal * $rates['technician'] / 100, 2);

                    $this->notificationService->notifyBalanceReleased(
                        $techPasang->user->fcm_token,
                        $techShare,
                        $order->id
                    );
                }
            }

            Mail::to($order->user->email)->queue(new WarrantyActiveMail($order->fresh()));

            return response()->json([
                'message'             => 'Pesanan dikonfirmasi. Masa garansi 7 hari aktif.',
                'warranty_expires_at' => now()->addDays(7)->toIso8601String(),
            ]);
        }

        // Response untuk perbaikan fase 2
        return response()->json([
            'message' => 'Pesanan selesai. Terima kasih sudah menggunakan layanan Dikari!',
        ]);
    }

    public function orderHistory(Request $request)
    {
        $user = $request->user();
        $bp   = $user->businessPartner;
        abort_if(!$bp, 403, 'Bukan akun Business Partner.');

        $orders = Order::with(['items.bpService.serviceType', 'address', 'technician.user'])
            ->where('bp_id', $bp->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'orders' => $orders->map(fn($o) => $this->formatOrder($o)),
            'meta'   => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'total'        => $orders->total(),
            ],
        ]);
    }

    public function balance(Request $request)
    {
        $user = $request->user();
        $bp   = $user->businessPartner;
        abort_if(!$bp, 403, 'Bukan akun Business Partner.');

        $transactions = \App\Models\BalanceTransaction::where('owner_type', \App\Models\BusinessPartner::class)
            ->where('owner_id', $bp->id)
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        return response()->json([
            'balance'      => (float) $bp->balance,
            'transactions' => $transactions,
        ]);
    }

    private function formatOrder(Order $order): array
    {
        return [
            'id'               => $order->id,
            'status'           => $order->status,
            'payment_status'   => $order->payment_status,
            'scheduled_date'   => $order->scheduled_date?->format('Y-m-d'),
            'scheduled_time'   => $order->scheduled_time,
            'total_amount'     => (float) $order->total_amount,
            'auto_complete_at' => $order->auto_complete_at?->toIso8601String(),
            'is_perbaikan'     => (bool) $order->is_perbaikan,
            'perbaikan_phase'  => $order->perbaikan_phase,
            'technician'       => $order->technician ? [
                'id'    => $order->technician->id,
                'name'  => $order->technician->user->name ?? '-',
                'grade' => $order->technician->grade,
            ] : null,
            'address'          => [
                'label'        => $order->address?->label,
                'full_address' => $order->address?->formatted_address,
                'city'         => $order->address?->city_name,
            ],
            'items'            => $order->items->map(fn($item) => [
                'name'     => $item->bpService?->serviceType?->name ?? '-',
                'quantity' => $item->quantity,
                'subtotal' => (float) $item->subtotal,
            ]),
            'created_at'       => $order->created_at?->format('Y-m-d H:i'),
        ];
    }
}
