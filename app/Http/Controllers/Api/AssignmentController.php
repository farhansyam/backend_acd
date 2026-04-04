<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderAssignment;
use App\Models\Technician;
use App\Services\BalanceService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssignmentController extends Controller
{
    public function __construct(
        private BalanceService $balanceService,
        private NotificationService $notificationService
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
            $order->update([
                'technician_id' => $technician->id,
                'status'        => 'in_progress',
            ]);
            OrderAssignment::create([
                'order_id'      => $order->id,
                'technician_id' => $technician->id,
                'assigned_by'   => $user->id,
                'status'        => 'assigned',
                'notes'         => $validated['notes'] ?? null,
            ]);
        });

        // Notifikasi teknisi
        if ($technician->user->fcm_token) {
            $this->notificationService->notifyTechnicianAssigned(
                $technician->user->fcm_token,
                $order->id,
                $order->address->city_name ?? '-'
            );
        }

        // Notifikasi customer
        if ($order->user->fcm_token) {
            $this->notificationService->notifyOrderConfirmed(
                $order->user->fcm_token,
                $order->id
            );
        }

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

        $autoCompleteAt = now()->addHours(2);

        $order->update([
            'status'           => 'waiting_confirmation',
            'auto_complete_at' => $autoCompleteAt,
        ]);

        OrderAssignment::where('order_id', $order->id)
            ->where('status', 'assigned')
            ->update(['status' => 'completed', 'completed_at' => now()]);

        // Notifikasi customer
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

    // Dipanggil dari app customer
    public function customerConfirm(Request $request, Order $order)
    {
        abort_if($order->user_id !== $request->user()->id, 403, 'Bukan order kamu.');
        abort_if($order->status !== 'waiting_confirmation', 422, 'Order tidak menunggu konfirmasi.');

        DB::transaction(function () use ($order) {
            $order->update([
                'status'           => 'completed',
                'auto_complete_at' => null,
            ]);
            $this->balanceService->distributeOrderEarning($order);
        });

        // Notifikasi teknisi
        $technician = $order->technician;
        if ($technician?->user->fcm_token) {
            $grade     = $technician->grade ?? 'beginner';
            $rates     = BalanceService::GRADE_RATES[$grade];
            $techShare = round((float) $order->total_amount * $rates['technician'] / 100, 2);
            $this->notificationService->notifyBalanceReleased(
                $technician->user->fcm_token,
                $techShare,
                $order->id
            );
        }

        return response()->json(['message' => 'Pesanan dikonfirmasi selesai. Terima kasih!']);
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
