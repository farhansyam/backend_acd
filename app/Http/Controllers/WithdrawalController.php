<?php

namespace App\Http\Controllers;

use App\Models\BalanceTransaction;
use App\Models\Technician;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    // ─── List semua withdrawal ────────────────────────────────
    public function index()
    {
        $withdrawals = Withdrawal::with(['technician.user'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('withdrawals.index', compact('withdrawals'));
    }

    // ─── Approve withdrawal ───────────────────────────────────
    public function approve(Request $request, Withdrawal $withdrawal)
    {
        abort_if($withdrawal->status !== 'pending', 422, 'Withdrawal sudah diproses.');

        $technician = $withdrawal->technician;
        abort_if($technician->balance < $withdrawal->amount, 422, 'Saldo teknisi tidak mencukupi.');

        DB::transaction(function () use ($withdrawal, $technician) {
            $balanceBefore = (float) $technician->balance;
            $technician->decrement('balance', $withdrawal->amount);

            BalanceTransaction::create([
                'owner_type'     => Technician::class,
                'owner_id'       => $technician->id,
                'type'           => 'withdraw',
                'amount'         => $withdrawal->amount,
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceBefore - $withdrawal->amount,
                'description'    => "Penarikan ke {$withdrawal->bank_name} - {$withdrawal->account_number} a/n {$withdrawal->account_name}",
                'status'         => 'completed',
            ]);

            $withdrawal->update([
                'status'      => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);
        });

        // TODO: notif ke teknisi (FCM + email)

        return redirect()
            ->route('withdrawals.index')
            ->with('success', "Withdrawal #$withdrawal->id berhasil disetujui.");
    }

    // ─── Reject withdrawal ────────────────────────────────────
    public function reject(Request $request, Withdrawal $withdrawal)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        abort_if($withdrawal->status !== 'pending', 422, 'Withdrawal sudah diproses.');

        $withdrawal->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'reviewed_by'      => Auth::id(),
            'reviewed_at'      => now(),
        ]);

        // TODO: notif ke teknisi (FCM + email)

        return redirect()
            ->route('withdrawals.index')
            ->with('success', "Withdrawal #$withdrawal->id ditolak.");
    }
}
