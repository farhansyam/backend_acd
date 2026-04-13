<?php

namespace App\Http\Controllers;

use App\Models\BalanceTransaction;
use App\Models\BusinessPartner;
use App\Models\Order;
use App\Models\Technician;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // ─── List semua pembayaran ────────────────────────────────
    public function payments()
    {
        $orders = Order::with(['user', 'businessPartner'])
            ->whereIn('payment_status', ['paid', 'expired', 'failed'])
            ->orderByDesc('paid_at')
            ->paginate(20);

        return view('admin.payments', compact('orders'));
    }

    // ─── List semua customer ──────────────────────────────────
    public function customers()
    {
        $customers = User::where('role', 'customer')
            ->withCount('orders')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.customers', compact('customers'));
    }

    // ─── List semua teknisi (lintas BP) ──────────────────────
    public function technicians()
    {
        $technicians = Technician::with(['user', 'businessPartner'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.technicians', compact('technicians'));
    }

    // ─── Wallet — saldo teknisi & BP ─────────────────────────
    public function wallets()
    {
        $technicians = Technician::with('user')
            ->where('status', 'approved')
            ->orderByDesc('balance')
            ->get();

        $businessPartners = BusinessPartner::with('user')
            ->orderByDesc('balance')
            ->get();

        $totalTechBalance = $technicians->sum('balance');
        $totalBpBalance   = $businessPartners->sum('balance');

        return view('admin.wallets', compact(
            'technicians',
            'businessPartners',
            'totalTechBalance',
            'totalBpBalance'
        ));
    }
}
