<?php

namespace App\Http\Controllers;

use App\Models\BalanceTransaction;
use App\Models\BusinessPartner;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\OrderRating;
use App\Models\Technician;
use App\Models\User;
use App\Models\Withdrawal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'adminsuper') {
            return $this->adminDashboard();
        }

        return $this->bpDashboard();
    }

    // ─── Dashboard Admin ──────────────────────────────────────
    private function adminDashboard()
    {
        $now        = Carbon::now();
        $thisMonth  = $now->copy()->startOfMonth();
        $lastMonth  = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // ─── Summary Cards ────────────────────────────────────
        $totalOrders      = Order::count();
        $ordersThisMonth  = Order::where('created_at', '>=', $thisMonth)->count();
        $ordersLastMonth  = Order::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();

        $totalRevenue     = Order::where('payment_status', 'paid')->sum('total_amount');
        $revenueThisMonth = Order::where('payment_status', 'paid')
            ->where('paid_at', '>=', $thisMonth)->sum('total_amount');
        $revenueLastMonth = Order::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$lastMonth, $lastMonthEnd])->sum('total_amount');

        $totalCustomers    = User::where('role', 'customer')->count();
        $totalTechnicians  = Technician::where('status', 'approved')->count();
        $pendingWithdrawals = Withdrawal::where('status', 'pending')->count();
        $openComplaints    = Complaint::whereIn('status', ['open', 'in_review', 'rework_assigned'])->count();

        // ─── Revenue Chart (12 bulan terakhir) ───────────────
        $revenueChart = Order::where('payment_status', 'paid')
            ->where('paid_at', '>=', $now->copy()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as month, SUM(total_amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($r) => [
                'month' => Carbon::parse($r->month . '-01')->format('M Y'),
                'total' => (float) $r->total,
            ]);

        // ─── Order Status Breakdown ───────────────────────────
        $orderStatus = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // ─── Top Teknisi (by order selesai bulan ini) ─────────
        $topTechnicians = Technician::with('user')
            ->withCount(['orders as completed_this_month' => function ($q) use ($thisMonth) {
                $q->where('status', 'completed')
                    ->where('updated_at', '>=', $thisMonth);
            }])
            ->orderByDesc('completed_this_month')
            ->take(5)
            ->get();

        // ─── Order Terbaru ────────────────────────────────────
        $recentOrders = Order::with(['user', 'businessPartner'])
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // ─── Growth % ─────────────────────────────────────────
        $orderGrowth  = $ordersLastMonth > 0
            ? round((($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth) * 100, 1)
            : 0;
        $revenueGrowth = $revenueLastMonth > 0
            ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
            : 0;

        return view('dashboard.admin', compact(
            'totalOrders',
            'ordersThisMonth',
            'orderGrowth',
            'totalRevenue',
            'revenueThisMonth',
            'revenueGrowth',
            'totalCustomers',
            'totalTechnicians',
            'pendingWithdrawals',
            'openComplaints',
            'revenueChart',
            'orderStatus',
            'topTechnicians',
            'recentOrders'
        ));
    }

    // ─── Dashboard BP ─────────────────────────────────────────
    private function bpDashboard()
    {
        $user = Auth::user();
        $bp   = BusinessPartner::where('user_id', $user->id)->firstOrFail();
        $now  = Carbon::now();
        $thisMonth = $now->copy()->startOfMonth();
        $lastMonth = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // ─── Summary Cards ────────────────────────────────────
        $ordersThisMonth  = Order::where('bp_id', $bp->id)
            ->where('created_at', '>=', $thisMonth)->count();
        $ordersLastMonth  = Order::where('bp_id', $bp->id)
            ->whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();

        $revenueThisMonth = Order::where('bp_id', $bp->id)
            ->where('payment_status', 'paid')
            ->where('paid_at', '>=', $thisMonth)->sum('total_amount');

        $pendingOrders    = Order::where('bp_id', $bp->id)
            ->where('status', 'confirmed')
            ->whereNull('technician_id')->count();

        $activeComplaints = Complaint::where('bp_id', $bp->id)
            ->whereIn('status', ['open', 'in_review', 'rework_assigned'])->count();

        $myTechnicians    = Technician::where('bp_id', $bp->id)
            ->where('status', 'approved')->count();

        $bpBalance        = (float) $bp->balance;

        // ─── Order Chart (6 bulan terakhir) ───────────────────
        $orderChart = Order::where('bp_id', $bp->id)
            ->where('created_at', '>=', $now->copy()->subMonths(5)->startOfMonth())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($r) => [
                'month' => Carbon::parse($r->month . '-01')->format('M Y'),
                'count' => (int) $r->count,
            ]);

        // ─── Top Teknisi di BP ini ────────────────────────────
        $topTechnicians = Technician::with('user')
            ->where('bp_id', $bp->id)
            ->where('status', 'approved')
            ->withCount(['orders as completed_this_month' => function ($q) use ($thisMonth) {
                $q->where('status', 'completed')
                    ->where('updated_at', '>=', $thisMonth);
            }])
            ->orderByDesc('avg_rating')
            ->take(5)
            ->get();

        // ─── Order terbaru di BP ini ──────────────────────────
        $recentOrders = Order::with(['user', 'technician.user'])
            ->where('bp_id', $bp->id)
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $orderGrowth = $ordersLastMonth > 0
            ? round((($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth) * 100, 1)
            : 0;

        return view('dashboard.bp', compact(
            'bp',
            'ordersThisMonth',
            'orderGrowth',
            'revenueThisMonth',
            'pendingOrders',
            'activeComplaints',
            'myTechnicians',
            'bpBalance',
            'orderChart',
            'topTechnicians',
            'recentOrders'
        ));
    }
}
