<?php

namespace App\Http\Controllers;

use App\Models\BalanceTransaction;
use App\Models\BusinessPartner;
use App\Models\Order;
use App\Models\OrderRating;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TechnicianController extends Controller
{
    private function getMyBp(): BusinessPartner
    {
        return BusinessPartner::where('user_id', Auth::id())->firstOrFail();
    }

    private function isBp(): bool
    {
        return Auth::user()->role === 'business_partner';
    }

    public function index()
    {
        if ($this->isBp()) {
            $bp = $this->getMyBp();
            $technicians = Technician::with('user')
                ->where('bp_id', $bp->id)
                ->where('status', 'approved')
                ->latest()
                ->paginate(10);
        } else {
            $technicians = Technician::with(['user', 'businessPartner'])
                ->where('status', 'approved')
                ->latest()
                ->paginate(10);
        }

        return view('bp-technicians.index', compact('technicians'));
    }

    public function show(Technician $technician)
    {
        $bp = $this->getMyBp();
        abort_if($technician->bp_id !== $bp->id, 403);

        $technician->load(['user', 'businessPartner']);

        $totalCompleted = Order::where('technician_id', $technician->id)
            ->where('status', 'completed')->count();

        $completedThisMonth = Order::where('technician_id', $technician->id)
            ->where('status', 'completed')
            ->where('updated_at', '>=', now()->startOfMonth())->count();

        $totalEarning = \App\Models\BalanceTransaction::where('owner_type', Technician::class)
            ->where('owner_id', $technician->id)
            ->whereIn('type', ['earning', 'release', 'rework_earning'])
            ->sum('amount');

        $recentRatings = \App\Models\OrderRating::with('order.user')
            ->where('technician_id', $technician->id)
            ->orderByDesc('created_at')->take(5)->get();

        $recentOrders = Order::with('user')
            ->where('technician_id', $technician->id)
            ->orderByDesc('created_at')->take(5)->get();

        return view('bp-technicians.show', compact(
            'technician',
            'totalCompleted',
            'completedThisMonth',
            'totalEarning',
            'recentRatings',
            'recentOrders'
        ));
    }

    public function updateGrade(Request $request, Technician $technician)
    {
        if ($this->isBp()) {
            $bp = $this->getMyBp();
            abort_if($technician->bp_id !== $bp->id, 403);
        }

        $request->validate([
            'grade' => 'required|in:beginner,medium,pro',
        ]);

        $oldGrade = $technician->grade;
        $technician->update(['grade' => $request->grade]);

        $action = $request->grade === 'pro' || $request->grade > $oldGrade
            ? 'dinaikkan' : 'diubah';

        return redirect()
            ->route('bp-technicians.show', $technician)
            ->with('success', "Grade teknisi berhasil {$action} menjadi " . ucfirst($request->grade) . ".");
    }

    public function suspend(Request $request, Technician $technician)
    {
        if ($this->isBp()) {
            $bp = $this->getMyBp();
            abort_if($technician->bp_id !== $bp->id, 403);
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $technician->update(['status' => 'rejected', 'rejection_reason' => $request->reason]);
        $technician->user->update(['is_active' => 0]);

        return redirect()
            ->route('bp-technicians.show', $technician)
            ->with('success', 'Teknisi berhasil disuspend.');
    }

    public function activate(Technician $technician)
    {
        if ($this->isBp()) {
            $bp = $this->getMyBp();
            abort_if($technician->bp_id !== $bp->id, 403);
        }

        $technician->update(['status' => 'approved', 'rejection_reason' => null]);
        $technician->user->update(['is_active' => 1]);

        return redirect()
            ->route('bp-technicians.show', $technician)
            ->with('success', 'Teknisi berhasil diaktifkan kembali.');
    }

    public function destroy(Technician $technician)
    {
        $bp = $this->getMyBp();
        abort_if($technician->bp_id !== $bp->id, 403);

        $technician->user->update(['is_active' => 0]);
        $technician->update(['status' => 'rejected', 'rejection_reason' => 'Dinonaktifkan oleh BP']);

        return redirect()->route('bp-technicians.index')
            ->with('success', 'Teknisi berhasil dinonaktifkan.');
    }

    public function approvalIndex()
    {
        $bp = $this->getMyBp();

        $pending = Technician::with('user')
            ->where('bp_id', $bp->id)
            ->where('status', 'pending')
            ->latest()
            ->paginate(10);

        return view('bp-technicians.approval', compact('pending'));
    }

    public function approve(Request $request, Technician $technician)
    {
        $bp = $this->getMyBp();
        abort_if($technician->bp_id !== $bp->id, 403);

        $request->validate([
            'grade' => 'required|in:beginner,medium,pro',
        ]);

        $technician->update([
            'status'      => 'approved',
            'grade'       => $request->grade,
            'approved_at' => now(),
        ]);

        $technician->user->update(['is_active' => 1]);

        return redirect()->route('bp-technicians.approval')
            ->with('success', 'Teknisi berhasil di-approve.');
    }

    public function reject(Request $request, Technician $technician)
    {
        $bp = $this->getMyBp();
        abort_if($technician->bp_id !== $bp->id, 403);

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $technician->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->route('bp-technicians.approval')
            ->with('success', 'Teknisi berhasil di-reject.');
    }

    public function toggleActive(Technician $technician)
    {
        $bp = $this->getMyBp();
        abort_if($technician->bp_id !== $bp->id, 403);

        $isActive = $technician->user->is_active;
        $technician->user->update(['is_active' => $isActive ? 0 : 1]);

        $message = $isActive ? 'Teknisi berhasil dinonaktifkan.' : 'Teknisi berhasil diaktifkan.';

        return redirect()->route('bp-technicians.index')->with('success', $message);
    }

    // API — riwayat withdrawal teknisi
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
