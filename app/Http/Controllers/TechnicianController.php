<?php

namespace App\Http\Controllers;

use App\Models\BusinessPartner;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TechnicianController extends Controller
{
    private function getMyBp()
    {
        return BusinessPartner::where('user_id', Auth::id())->firstOrFail();
    }

    public function index()
    {
        $bp = $this->getMyBp();

        $technicians = Technician::with('user')
            ->where('bp_id', $bp->id)
            ->where('status', 'approved')
            ->latest()
            ->paginate(10);

        return view('bp-technicians.index', compact('technicians'));
    }

    public function show(Technician $technician)
    {
        $bp = $this->getMyBp();

        // Pastikan teknisi ini milik BP yang login
        abort_if($technician->bp_id !== $bp->id, 403);

        return view('bp-technicians.show', compact('technician'));
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

    // Halaman approval (pending)
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
}
