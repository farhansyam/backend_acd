<?php

namespace App\Http\Controllers;

use App\Models\BusinessPartner;
use App\Models\Order;
use App\Models\OrderAssignment;
use App\Models\Technician;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderAssignController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    private function getMyBp(): BusinessPartner
    {
        return BusinessPartner::where('user_id', Auth::id())->firstOrFail();
    }

    // ─── List order yang bisa di-assign ──────────────────────
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'adminsuper') {
            $orders = Order::with(['businessPartner', 'address', 'items.bpService.serviceType', 'technician.user'])
                ->whereIn('status', ['confirmed', 'in_progress', 'waiting_confirmation', 'completed'])
                ->where('payment_status', 'paid')
                ->orderByDesc('created_at')
                ->paginate(15);
        } else {
            $bp = $this->getMyBp();
            $orders = Order::with(['address', 'items.bpService.serviceType', 'technician.user'])
                ->where('bp_id', $bp->id)
                ->whereIn('status', ['confirmed', 'in_progress', 'waiting_confirmation', 'completed'])
                ->where('payment_status', 'paid')
                ->orderByDesc('created_at')
                ->paginate(15);
        }

        return view('orders.index', compact('orders'));
    }

    // ─── Form assign teknisi ──────────────────────────────────
    public function show(Order $order)
    {
        $user = Auth::user();

        if ($user->role !== 'adminsuper') {
            $bp = $this->getMyBp();
            abort_if($order->bp_id !== $bp->id, 403);
        }

        $order->load(['address', 'items.bpService.serviceType', 'technician.user', 'user']);

        $technicians = collect();
        if ($user->role === 'adminsuper') {
            $technicians = Technician::with('user')
                ->where('bp_id', $order->bp_id)
                ->where('status', 'approved')
                ->get();
        } else {
            $bp = $this->getMyBp();
            $technicians = Technician::with('user')
                ->where('bp_id', $bp->id)
                ->where('status', 'approved')
                ->get();
        }

        return view('orders.show', compact('order', 'technicians'));
    }

    // ─── Assign teknisi ke order ──────────────────────────────
    public function assign(Request $request, Order $order)
    {

        \Log::info('Assign attempt', [
            'order_id'       => $order->id,
            'order_status'   => $order->status,
            'technician_id'  => $request->technician_id,
            'user_id'        => Auth::id(),
        ]);
        $request->validate([
            'technician_id' => 'required|exists:technicians,id',
            'notes'         => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        if ($user->role !== 'adminsuper') {
            $bp = $this->getMyBp();
            abort_if($order->bp_id !== $bp->id, 403);
        }

        abort_if($order->technician_id, 422, 'Order sudah di-assign.');
        abort_if(
            !in_array($order->status, ['confirmed', 'in_progress']),
            422,
            'Order tidak bisa di-assign.'
        );
        abort_if($order->technician_id, 422, 'Order sudah di-assign.');



        $technician = Technician::with('user')
            ->where('id', $request->technician_id)
            ->where('status', 'approved')
            ->firstOrFail();

        DB::transaction(function () use ($order, $technician, $request) {
            $order->update([
                'technician_id' => $technician->id,
                'status'        => 'in_progress',
            ]);

            OrderAssignment::create([
                'order_id'      => $order->id,
                'technician_id' => $technician->id,
                'assigned_by'   => Auth::id(),
                'status'        => 'assigned',
                'notes'         => $request->notes,
            ]);
        });

        // Notifikasi ke teknisi
        if ($technician->user->fcm_token) {
            $this->notificationService->notifyTechnicianAssigned(
                $technician->user->fcm_token,
                $order->id,
                $order->address->city_name ?? '-'
            );
        }

        // Notifikasi ke customer
        if ($order->user->fcm_token) {
            $this->notificationService->notifyOrderConfirmed(
                $order->user->fcm_token,
                $order->id
            );
        }

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Teknisi berhasil di-assign ke order ini.');
    }
}
