<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionSession;
use App\Models\Technician;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionWebController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    // ─── GET list semua langganan ─────────────────────────────
    public function index(Request $request)
    {
        $user = $request->user();
        $isBp = $user->role === 'business_partner';

        $query = Subscription::with(['user', 'package', 'address', 'sessions', 'businessPartner'])
            ->latest();

        // BP hanya lihat langganan miliknya
        if ($isBp) {
            $bp = $user->businessPartner;
            abort_if(!$bp, 403);
            $query->where('bp_id', $bp->id);
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter payment_status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter package
        if ($request->filled('package_type')) {
            $query->whereHas('package', fn($q) => $q->where('type', $request->package_type));
        }

        $subscriptions = $query->paginate(20)->withQueryString();

        return view('subscriptions.index', compact('subscriptions'));
    }

    // ─── GET detail langganan + sesi ─────────────────────────
    public function show(Request $request, Subscription $subscription)
    {
        $user = $request->user();

        // BP hanya bisa lihat miliknya
        if ($user->role === 'business_partner') {
            $bp = $user->businessPartner;
            abort_if($subscription->bp_id !== $bp->id, 403);
        }

        $subscription->load([
            'user',
            'package',
            'address',
            'userPhone',
            'items.bpService.serviceType',
            'sessions.technician.user',
            'sessions.report',
        ]);

        // Ambil teknisi yang tersedia untuk BP ini
        $technicians = Technician::with('user')
            ->where('bp_id', $subscription->bp_id)
            ->where('status', 'approved')
            ->get();

        return view('subscriptions.show', compact('subscription', 'technicians'));
    }

    // ─── POST assign teknisi ke sesi ─────────────────────────
    public function assignSession(Request $request, Subscription $subscription, SubscriptionSession $session)
    {
        $request->validate([
            'technician_id' => 'required|exists:technicians,id',
        ]);

        $user = $request->user();

        // BP hanya bisa assign ke langganan miliknya
        if ($user->role === 'business_partner') {
            $bp = $user->businessPartner;
            abort_if($subscription->bp_id !== $bp->id, 403);
        }

        abort_if($session->subscription_id !== $subscription->id, 403);
        abort_if(
            !in_array($session->status, ['scheduled', 'confirmed']),
            422,
            'Sesi tidak bisa di-assign.'
        );
        abort_if(
            $subscription->payment_status !== 'paid',
            422,
            'Langganan belum dibayar.'
        );

        $technician = Technician::with('user')
            ->where('id', $request->technician_id)
            ->where('bp_id', $subscription->bp_id)
            ->where('status', 'approved')
            ->firstOrFail();

        DB::transaction(function () use ($session, $technician) {
            $session->update([
                'technician_id' => $technician->id,
                'status'        => 'confirmed',
            ]);
        });

        // Notif teknisi
        if ($technician->user?->fcm_token) {
            $this->notificationService->notifyTechnicianAssigned(
                $technician->user->fcm_token,
                $session->id,
                $subscription->address?->city_name ?? '-'
            );
        }

        // Notif customer
        if ($subscription->user?->fcm_token) {
            $this->notificationService->notifyOrderConfirmed(
                $subscription->user->fcm_token,
                $session->id
            );
        }

        return redirect()
            ->route('subscriptions.show', $subscription)
            ->with('success', "Teknisi {$technician->user->name} berhasil di-assign ke sesi ke-{$session->session_number}.");
    }
}
