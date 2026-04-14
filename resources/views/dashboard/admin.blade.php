@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard')

@section('content')

{{-- Summary Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-6 mb-6">

    <div class="card border-0">
        <div class="card-body p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center">
                    <iconify-icon icon="hugeicons:invoice-03" class="text-primary-600 text-2xl"></iconify-icon>
                </div>
                <span class="text-sm font-bold {{ $orderGrowth >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                    {{ $orderGrowth >= 0 ? '+' : '' }}{{ $orderGrowth }}%
                </span>
            </div>
            <p class="text-2xl font-bold mb-1">{{ number_format($ordersThisMonth) }}</p>
            <p class="text-sm text-secondary-light">Order Bulan Ini</p>
            <p class="text-xs text-secondary-light mt-1">Total: {{ number_format($totalOrders) }} order</p>
        </div>
    </div>

    <div class="card border-0">
        <div class="card-body p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-success-100 rounded-xl flex items-center justify-center">
                    <iconify-icon icon="hugeicons:money-send-square" class="text-success-600 text-2xl"></iconify-icon>
                </div>
                <span class="text-sm font-bold {{ $revenueGrowth >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                    {{ $revenueGrowth >= 0 ? '+' : '' }}{{ $revenueGrowth }}%
                </span>
            </div>
            <p class="text-xl font-bold mb-1">Rp {{ number_format($revenueThisMonth, 0, ',', '.') }}</p>
            <p class="text-sm text-secondary-light">Revenue Bulan Ini</p>
            <p class="text-xs text-secondary-light mt-1">Total: Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="card border-0">
        <div class="card-body p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-warning-100 rounded-xl flex items-center justify-center">
                    <iconify-icon icon="lucide:users" class="text-warning-600 text-2xl"></iconify-icon>
                </div>
            </div>
            <p class="text-2xl font-bold mb-1">{{ number_format($totalCustomers) }}</p>
            <p class="text-sm text-secondary-light">Total Customer</p>
            <p class="text-xs text-secondary-light mt-1">{{ $totalTechnicians }} teknisi aktif</p>
        </div>
    </div>

    <div class="card border-0">
        <div class="card-body p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-danger-100 rounded-xl flex items-center justify-center">
                    <iconify-icon icon="lucide:alert-circle" class="text-danger-600 text-2xl"></iconify-icon>
                </div>
            </div>
            <p class="text-2xl font-bold mb-1">{{ $openComplaints }}</p>
            <p class="text-sm text-secondary-light">Komplain Aktif</p>
            <p class="text-xs text-secondary-light mt-1">{{ $pendingWithdrawals }} penarikan pending</p>
        </div>
    </div>

</div>

<div class="grid grid-cols-12 gap-6 mb-6">

    {{-- Revenue Chart --}}
    <div class="col-span-12 lg:col-span-8">
        <div class="card border-0 h-full">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-4 px-6 flex items-center justify-between">
                <h6 class="font-semibold mb-0">Revenue 12 Bulan Terakhir</h6>
            </div>
            <div class="card-body p-6">
                <canvas id="revenueChart" height="120"></canvas>
            </div>
        </div>
    </div>

    {{-- Order Status --}}
    <div class="col-span-12 lg:col-span-4">
        <div class="card border-0 h-full">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-4 px-6">
                <h6 class="font-semibold mb-0">Status Order</h6>
            </div>
            <div class="card-body p-6">
                <canvas id="statusChart" height="200"></canvas>
            </div>
        </div>
    </div>

</div>

<div class="grid grid-cols-12 gap-6">

    {{-- Top Teknisi --}}
    <div class="col-span-12 lg:col-span-5">
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-4 px-6">
                <h6 class="font-semibold mb-0">Top Teknisi Bulan Ini</h6>
            </div>
            <div class="card-body p-0">
                <ul class="divide-y divide-neutral-100 dark:divide-neutral-700">
                    @forelse($topTechnicians as $i => $tech)
                    <li class="flex items-center gap-3 px-6 py-4">
                        <span class="w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold
                            {{ $i === 0 ? 'bg-warning-100 text-warning-600' : 'bg-neutral-100 text-neutral-500' }}">
                            {{ $i + 1 }}
                        </span>
                        <div class="w-9 h-9 rounded-full bg-primary-100 flex items-center justify-center font-bold text-primary-600 text-sm">
                            {{ strtoupper(substr($tech->user->name ?? 'T', 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-sm">{{ $tech->user->name ?? '-' }}</p>
                            <p class="text-xs text-secondary-light capitalize">{{ $tech->grade }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-sm">{{ $tech->completed_this_month }} order</p>
                            <p class="text-xs text-warning-600">⭐ {{ number_format($tech->avg_rating, 1) }}</p>
                        </div>
                    </li>
                    @empty
                    <li class="px-6 py-8 text-center text-secondary-light text-sm">Belum ada data.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    {{-- Order Terbaru --}}
    <div class="col-span-12 lg:col-span-7">
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-4 px-6 flex items-center justify-between">
                <h6 class="font-semibold mb-0">Order Terbaru</h6>
                <a href="{{ route('orders.index') }}" class="text-sm text-primary-600 font-medium">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table bordered-table style-two mb-0">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Mitra</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                            @php
                                $statusColors = [
                                    'pending'              => 'bg-warning-100 text-warning-600',
                                    'confirmed'            => 'bg-info-100 text-info-600',
                                    'in_progress'          => 'bg-purple-100 text-purple-600',
                                    'waiting_confirmation' => 'bg-info-100 text-info-600',
                                    'warranty'             => 'bg-success-100 text-success-600',
                                    'complained'           => 'bg-danger-100 text-danger-600',
                                    'completed'            => 'bg-success-100 text-success-600',
                                    'cancelled'            => 'bg-neutral-100 text-neutral-500',
                                ];
                                $sc = $statusColors[$order->status] ?? 'bg-neutral-100 text-neutral-500';
                            @endphp
                            <tr>
                                <td class="font-medium">#{{ $order->id }}</td>
                                <td>{{ $order->user->name ?? '-' }}</td>
                                <td>{{ $order->businessPartner->name ?? '-' }}</td>
                                <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                <td>
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $sc }}">
                                        {{ $order->status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary-light py-4">Belum ada order.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($revenueChart->pluck('month')) !!},
        datasets: [{
            label: 'Revenue',
            data: {!! json_encode($revenueChart->pluck('total')) !!},
            borderColor: '#1976D2',
            backgroundColor: 'rgba(25, 118, 210, 0.08)',
            borderWidth: 2.5,
            pointBackgroundColor: '#1976D2',
            pointRadius: 4,
            fill: true,
            tension: 0.4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: v => 'Rp ' + (v/1000000).toFixed(1) + 'jt'
                }
            }
        }
    }
});

// Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusData = {!! json_encode($orderStatus) !!};
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: Object.keys(statusData),
        datasets: [{
            data: Object.values(statusData),
            backgroundColor: [
                '#FF9800', '#2196F3', '#9C27B0',
                '#00BCD4', '#009688', '#FF5722',
                '#4CAF50', '#9E9E9E'
            ],
            borderWidth: 2,
            borderColor: '#fff',
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 11 } } }
        }
    }
});
</script>
@endpush
@endsection