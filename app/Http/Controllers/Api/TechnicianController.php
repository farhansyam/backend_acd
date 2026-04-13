<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BalanceTransaction;
use App\Models\Order;
use App\Models\OrderAssignment;
use App\Models\OrderReport;
use App\Models\Technician;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use App\Mail\WaitingConfirmationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TechnicianController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    // ─── GET order yang di-assign ke teknisi ──────────────────
    public function myOrders(Request $request)
    {
        $user       = $request->user();
        $technician = Technician::where('user_id', $user->id)->first();

        abort_if(!$technician, 403, 'Bukan akun teknisi.');

        $orders = Order::with(['items.bpService.serviceType', 'address', 'phone', 'user'])
            ->where('technician_id', $technician->id)
            ->whereIn('status', [
                'confirmed',
                'in_progress',
                'waiting_confirmation',
                'completed',
            ])
            ->orderByDesc('scheduled_date')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($o) => $this->formatOrder($o));

        return response()->json(['orders' => $orders]);
    }

    // ─── GET detail order ─────────────────────────────────────
    public function showOrder(Request $request, Order $order)
    {
        $user       = $request->user();
        $technician = Technician::where('user_id', $user->id)->first();

        abort_if(!$technician, 403, 'Bukan akun teknisi.');
        abort_if($order->technician_id !== $technician->id, 403, 'Bukan order kamu.');

        $order->load(['items.bpService.serviceType', 'address', 'phone', 'user', 'report']);

        return response()->json(['order' => $this->formatOrder($order)]);
    }

    // ─── POST submit laporan + tandai selesai ─────────────────
    public function submitReport(Request $request, Order $order)
    {
        $request->validate([
            'photo_before'       => 'required|image|max:5120',
            'photo_after'        => 'required|image|max:5120',
            'notes'              => 'nullable|string|max:1000',
            'filter_cleaned'     => 'boolean',
            'freon_checked'      => 'boolean',
            'drain_cleaned'      => 'boolean',
            'electrical_checked' => 'boolean',
        ]);

        $user       = $request->user();
        $technician = Technician::where('user_id', $user->id)->first();

        abort_if(!$technician, 403, 'Bukan akun teknisi.');
        abort_if($order->technician_id !== $technician->id, 403, 'Bukan order kamu.');
        abort_if(
            !in_array($order->status, ['confirmed', 'in_progress']),
            422,
            'Order tidak dalam status yang bisa diselesaikan.'
        );

        // Upload foto
        $photoBefore = $request->file('photo_before')
            ->store("order-reports/{$order->id}", 'public');
        $photoAfter = $request->file('photo_after')
            ->store("order-reports/{$order->id}", 'public');

        $autoCompleteAt = now()->addMinutes(30);

        DB::transaction(function () use (
            $order,
            $technician,
            $request,
            $photoBefore,
            $photoAfter,
            $autoCompleteAt
        ) {
            // Simpan laporan
            OrderReport::create([
                'order_id'           => $order->id,
                'technician_id'      => $technician->id,
                'photo_before'       => $photoBefore,
                'photo_after'        => $photoAfter,
                'notes'              => $request->notes,
                'filter_cleaned'     => $request->boolean('filter_cleaned'),
                'freon_checked'      => $request->boolean('freon_checked'),
                'drain_cleaned'      => $request->boolean('drain_cleaned'),
                'electrical_checked' => $request->boolean('electrical_checked'),
            ]);

            // Update status order
            $order->update([
                'status'           => 'waiting_confirmation',
                'auto_complete_at' => $autoCompleteAt,
            ]);

            // Update assignment
            OrderAssignment::where('order_id', $order->id)
                ->where('status', 'assigned')
                ->update(['status' => 'completed', 'completed_at' => now()]);
        });

        // Notifikasi ke customer
        if ($order->user->fcm_token) {
            $this->notificationService->notifyWaitingConfirmation(
                $order->user->fcm_token,
                $order->id
            );
        }
        Mail::to($order->user->email)->queue(new WaitingConfirmationMail($order));

        return response()->json([
            'message'          => 'Laporan berhasil dikirim. Menunggu konfirmasi customer.',
            'auto_complete_at' => $autoCompleteAt->toIso8601String(),
        ]);
    }

    // ─── GET saldo & riwayat transaksi ────────────────────────
    public function balance(Request $request)
    {
        $user       = $request->user();
        $technician = Technician::where('user_id', $user->id)->first();

        abort_if(!$technician, 403, 'Bukan akun teknisi.');

        $transactions = BalanceTransaction::where('owner_type', Technician::class)
            ->where('owner_id', $technician->id)
            ->orderByDesc('created_at')
            ->take(30)
            ->get()
            ->map(fn($t) => [
                'id'             => $t->id,
                'type'           => $t->type,
                'amount'         => (float) $t->amount,
                'balance_before' => (float) $t->balance_before,
                'balance_after'  => (float) $t->balance_after,
                'description'    => $t->description,
                'status'         => $t->status,
                'release_at'     => $t->release_at?->format('Y-m-d H:i'),
                'created_at'     => $t->created_at->format('Y-m-d H:i'),
            ]);

        return response()->json([
            'balance'      => (float) $technician->balance,
            'balance_hold' => (float) $technician->balance_hold,
            'transactions' => $transactions,
        ]);
    }

    // ─── POST ajukan withdraw ─────────────────────────────────
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount'         => 'required|numeric|min:50000',
            'bank_name'      => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_name'   => 'required|string|max:100',
        ]);

        $user       = $request->user();
        $technician = Technician::where('user_id', $user->id)->first();

        abort_if(!$technician, 403, 'Bukan akun teknisi.');

        $amount = (float) $request->amount;

        // Cek saldo tersedia (dikurangi pending withdrawal)
        $pendingTotal = \App\Models\Withdrawal::where('technician_id', $technician->id)
            ->where('status', 'pending')
            ->sum('amount');

        $availableBalance = $technician->balance - $pendingTotal;

        abort_if(
            $availableBalance < $amount,
            422,
            'Saldo tidak mencukupi. Saldo tersedia: Rp ' . number_format($availableBalance, 0, ',', '.')
        );

        $withdrawal = \App\Models\Withdrawal::create([
            'technician_id'  => $technician->id,
            'amount'         => $amount,
            'bank_name'      => $request->bank_name,
            'account_number' => $request->account_number,
            'account_name'   => $request->account_name,
            'status'         => 'pending',
        ]);

        return response()->json([
            'message'       => 'Permintaan penarikan berhasil diajukan. Menunggu persetujuan admin.',
            'withdrawal_id' => $withdrawal->id,
        ]);
    }

    // ─── GET dashboard ────────────────────────────────────────
    public function dashboard(Request $request)
    {
        $user       = $request->user();
        $technician = Technician::where('user_id', $user->id)->first();

        abort_if(!$technician, 403, 'Bukan akun teknisi.');

        $now = now();

        $completedThisMonth = Order::where('technician_id', $technician->id)
            ->where('status', 'completed')
            ->whereMonth('updated_at', $now->month)
            ->whereYear('updated_at', $now->year)
            ->count();

        $earningThisMonth = BalanceTransaction::where('owner_type', Technician::class)
            ->where('owner_id', $technician->id)
            ->where('type', 'release')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->sum('amount');

        $activeOrder = Order::with(['items.bpService.serviceType', 'address'])
            ->where('technician_id', $technician->id)
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->latest()
            ->first();

        return response()->json([
            'technician' => [
                'name'         => $user->name,
                'grade'        => $technician->grade,
                'balance'      => (float) $technician->balance,
                'balance_hold' => (float) $technician->balance_hold,
            ],
            'stats' => [
                'completed_this_month' => $completedThisMonth,
                'earning_this_month'   => (float) $earningThisMonth,
            ],
            'active_order' => $activeOrder ? [
                'id'             => $activeOrder->id,
                'status'         => $activeOrder->status,
                'scheduled_date' => $activeOrder->scheduled_date?->format('Y-m-d'),
                'scheduled_time' => $activeOrder->scheduled_time,
                'total_amount'   => (float) $activeOrder->total_amount,
                'address'        => [
                    'label'        => $activeOrder->address?->label,
                    'full_address' => $activeOrder->address?->formatted_address,
                    'city'         => $activeOrder->address?->city_name,
                ],
                'items' => $activeOrder->items->map(fn($item) => [
                    'name'     => $item->bpService?->serviceType?->name ?? '-',
                    'quantity' => $item->quantity,
                ]),
            ] : null,
        ]);
    }

    // ─── Helper format order ──────────────────────────────────
    private function formatOrder(Order $order): array
    {
        $report = $order->report ?? $order->load('report')->report;

        return [
            'id'               => $order->id,
            'status'           => $order->status,
            'payment_status'   => $order->payment_status,
            'scheduled_date'   => $order->scheduled_date?->format('Y-m-d'),
            'scheduled_time'   => $order->scheduled_time,
            'total_amount'     => (float) $order->total_amount,
            'auto_complete_at' => $order->auto_complete_at?->toIso8601String(),
            'notes'            => $order->notes,
            'customer'         => [
                'name'  => $order->user?->name ?? '-',
                'email' => $order->user?->email ?? '-',
            ],
            'phone'            => [
                'label'        => $order->phone?->label ?? '-',
                'phone_number' => $order->phone?->phone_number ?? '-',
            ],
            'address'          => [
                'label'        => $order->address?->label ?? '-',
                'full_address' => $order->address?->formatted_address ?? '-',
                'city'         => $order->address?->city_name ?? '-',
                'notes'        => $order->address?->notes ?? '',
                'latitude'     => $order->address?->latitude,
                'longitude'    => $order->address?->longitude,
            ],
            'items'            => $order->items->map(fn($item) => [
                'name'       => $item->bpService?->serviceType?->name ?? '-',
                'quantity'   => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'subtotal'   => (float) $item->subtotal,
            ]),
            'report'           => $report ? [
                'photo_before'       => Storage::url($report->photo_before),
                'photo_after'        => Storage::url($report->photo_after),
                'notes'              => $report->notes,
                'filter_cleaned'     => $report->filter_cleaned,
                'freon_checked'      => $report->freon_checked,
                'drain_cleaned'      => $report->drain_cleaned,
                'electrical_checked' => $report->electrical_checked,
            ] : null,
            'created_at'       => $order->created_at?->format('Y-m-d H:i'),
        ];
    }

    // GET profil teknisi
    public function profile(Request $request)
    {
        $user       = $request->user();
        $technician = Technician::where('user_id', $user->id)->first();

        abort_if(!$technician, 403, 'Bukan akun teknisi.');

        $districts = $technician->districts;
        if (is_string($districts)) {
            $districts = json_decode($districts, true) ?? [];
        }

        // Stats all time
        $totalCompleted = Order::where('technician_id', $technician->id)
            ->where('status', 'completed')
            ->count();

        $totalEarning = BalanceTransaction::where('owner_type', Technician::class)
            ->where('owner_id', $technician->id)
            ->where('type', 'release')
            ->sum('amount');

        return response()->json([
            'user' => [
                'id'        => $user->id,
                'tech_id'   => $technician->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'role'      => $user->role,
                'balance'   => (float) $technician->balance,
                'grade'     => $technician->grade,
                'bp_id'     => $technician->bp_id,
                'city'      => $technician->city,
                'province'  => $technician->province,
                'districts' => $districts,
                'status'    => $technician->status,
                'avg_rating' => (float) $technician->avg_rating,

            ],
            'stats' => [
                'total_completed' => $totalCompleted,
                'total_earning'   => (float) $totalEarning,
            ],
        ]);
    }

    // PATCH update kecamatan
    public function updateDistricts(Request $request)
    {
        $request->validate([
            'districts'   => 'required|array|min:1',
            'districts.*' => 'required|string',
        ]);

        $user       = $request->user();
        $technician = Technician::where('user_id', $user->id)->first();

        abort_if(!$technician, 403, 'Bukan akun teknisi.');

        $technician->update(['districts' => $request->districts]);

        $districts = $technician->fresh()->districts;
        if (is_string($districts)) {
            $districts = json_decode($districts, true) ?? [];
        }

        return response()->json([
            'message'   => 'Kecamatan berhasil diperbarui.',
            'districts' => $districts,
        ]);
    }

    // PATCH ubah password
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        abort_if(
            !\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password),
            422,
            'Password lama tidak sesuai.'
        );

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->new_password),
        ]);

        return response()->json(['message' => 'Password berhasil diubah.']);
    }

    // GET riwayat withdrawal
    public function withdrawals(Request $request)
    {
        $user       = $request->user();
        $technician = Technician::where('user_id', $user->id)->first();

        abort_if(!$technician, 403, 'Bukan akun teknisi.');

        $withdrawals = \App\Models\Withdrawal::where('technician_id', $technician->id)
            ->orderByDesc('created_at')
            ->take(10)
            ->get()
            ->map(fn($w) => [
                'id'               => $w->id,
                'amount'           => (float) $w->amount,
                'bank_name'        => $w->bank_name,
                'account_number'   => $w->account_number,
                'account_name'     => $w->account_name,
                'status'           => $w->status,
                'status_label'     => $w->status_label,
                'rejection_reason' => $w->rejection_reason,
                'reviewed_at'      => $w->reviewed_at?->format('Y-m-d H:i'),
                'created_at'       => $w->created_at->format('Y-m-d H:i'),
            ]);

        return response()->json(['withdrawals' => $withdrawals]);
    }
}
