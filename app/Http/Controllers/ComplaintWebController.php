<?php

namespace App\Http\Controllers;

use App\Models\BalanceTransaction;
use App\Models\BusinessPartner;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\Technician;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ComplaintWebController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    private function getMyBp(): BusinessPartner
    {
        return BusinessPartner::where('user_id', Auth::id())->firstOrFail();
    }

    // ─── List komplain/garansi ────────────────────────────────
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'adminsuper') {
            $complaints = Complaint::with(['order', 'user', 'technician.user', 'reworkTechnician.user'])
                ->orderByDesc('created_at')
                ->paginate(15);
        } else {
            $bp = $this->getMyBp();
            $complaints = Complaint::with(['order', 'user', 'technician.user', 'reworkTechnician.user'])
                ->where('bp_id', $bp->id)
                ->orderByDesc('created_at')
                ->paginate(15);
        }

        return view('complaints.index', compact('complaints'));
    }

    // ─── Detail komplain ──────────────────────────────────────
    public function show(Complaint $complaint)
    {
        $user = Auth::user();
        if ($user->role !== 'adminsuper') {
            $bp = $this->getMyBp();
            abort_if($complaint->bp_id !== $bp->id, 403);
        }

        $complaint->load(['order', 'user', 'technician.user', 'reworkTechnician.user']);

        // Teknisi yang bisa di-assign rework (medium/pro, dari BP yang sama)
        $technicians = Technician::with('user')
            ->where('bp_id', $complaint->bp_id)
            ->where('status', 'approved')
            ->where(function ($q) use ($complaint) {
                $q->where('id', $complaint->technician_id) // teknisi pertama selalu ada
                    ->orWhereIn('grade', ['medium', 'pro']);   // atau medium/pro
            })
            ->get();

        return view('complaints.show', compact('complaint', 'technicians'));
    }

    // ─── Update status + assign teknisi rework ────────────────
    public function update(Request $request, Complaint $complaint)
    {

        \Log::info('Complaint update', [
            'complaint_id' => $complaint->id,
            'action'       => $request->action,
            'user_id'      => Auth::id(),
        ]);

        $user = Auth::user();
        if ($user->role !== 'adminsuper') {
            $bp = $this->getMyBp();
            \Log::info('BP check', [
                'complaint_bp_id' => $complaint->bp_id,
                'bp_id'           => $bp->id,
                'match'           => $complaint->bp_id === $bp->id,
            ]);
            abort_if($complaint->bp_id !== $bp->id, 403);
        }

        \Log::info('Before validate', ['all' => $request->all()]);

        $request->validate([
            'action'               => 'required|in:review,assign_rework,close',
            'rework_technician_id' => 'nullable|required_if:action,assign_rework|exists:technicians,id',
            'bp_comment'           => 'nullable|string|max:1000',
        ]);

        $action = $request->action;

        if ($action === 'review') {
            $complaint->update([
                'status'     => 'in_review',
                'bp_comment' => $request->bp_comment,
            ]);
        }

        if ($action === 'assign_rework') {
            $techId = $request->rework_technician_id;
            $tech   = Technician::findOrFail($techId);

            // abort_if(
            //     !in_array($tech->grade, ['medium', 'pro']),
            //     422,
            //     'Teknisi rework harus grade Medium atau Pro.'
            // );

            $isSameTech = (int)$techId === (int)$complaint->technician_id;
            if (!$isSameTech) {
                abort_if(
                    !in_array($tech->grade, ['medium', 'pro']),
                    422,
                    'Teknisi rework selain teknisi pertama harus grade Medium atau Pro.'
                );
            }

            $complaint->update([
                'status'               => 'rework_assigned',
                'rework_technician_id' => $techId,
                'assigned_by'          => Auth::id(),
                'bp_comment'           => $request->bp_comment ?? $complaint->bp_comment,
            ]);

            // Notif ke teknisi rework
            if ($tech->user->fcm_token) {
                $this->notificationService->notifyTechnicianAssigned(
                    $tech->user->fcm_token,
                    $complaint->order_id,
                    $complaint->order->address?->city_name ?? '-'
                );
            }
        }

        if ($action === 'close') {
            DB::transaction(function () use ($complaint, $request) {
                $complaint->update([
                    'status'      => 'closed',
                    'bp_comment'  => $request->bp_comment ?? $complaint->bp_comment,
                    'resolved_at' => now(),
                ]);

                $complaint->order->update(['status' => 'completed']);

                // Distribusi saldo rework kalau teknisi berbeda
                $isSameTech = $complaint->rework_technician_id === $complaint->technician_id;
                if ($complaint->rework_technician_id && !$isSameTech) {
                    $this->distributeReworkEarning($complaint);
                }
            });

            // Notif ke customer
            if ($complaint->user->fcm_token) {
                $this->notificationService->notifyOrderCompleted(
                    $complaint->user->fcm_token,
                    $complaint->order_id
                );
            }
        }

        return redirect()
            ->route('complaints.show', $complaint)
            ->with('success', 'Status komplain berhasil diperbarui.');
    }

    // ─── Distribusi saldo rework ──────────────────────────────
    private function distributeReworkEarning(Complaint $complaint): void
    {
        $order      = $complaint->order;
        $tech       = $complaint->reworkTechnician;
        $totalAmount = (float) $order->total_amount;

        // Hitung earning berdasarkan grade teknisi rework
        $percentage = match ($tech->grade) {
            'medium' => 0.65,
            'pro'    => 0.70,
            default  => 0.55,
        };

        $earning = round($totalAmount * $percentage, 2);

        // Catat biaya rework
        $complaint->update([
            'rework_cost'    => $earning,
            'rework_earning' => $earning,
        ]);

        // Tambah saldo teknisi rework (langsung, tanpa hold)
        $balanceBefore = (float) $tech->balance;
        $tech->increment('balance', $earning);

        BalanceTransaction::create([
            'owner_type'     => Technician::class,
            'owner_id'       => $tech->id,
            'type'           => 'rework_earning',
            'amount'         => $earning,
            'balance_before' => $balanceBefore,
            'balance_after'  => $balanceBefore + $earning,
            'description'    => "Pendapatan rework Order #{$order->id}",
            'status'         => 'completed',
        ]);
    }
}
