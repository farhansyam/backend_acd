@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">CRM</li>
@endsection

@section('content')

{{-- Stat Cards --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-6">
    <div class="lg:col-span-12 2xl:col-span-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">

            {{-- Total Order --}}
            <div class="card px-4 py-5 shadow-2 rounded-lg border-gray-200 dark:border-neutral-600 h-full bg-gradient-to-l from-primary-600/10 to-bg-white">
                <div class="card-body p-0">
                    <div class="flex flex-wrap items-center justify-between gap-1 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="mb-0 w-[44px] h-[44px] bg-primary-600 shrink-0 text-white flex justify-center items-center rounded-full h6">
                                <iconify-icon icon="hugeicons:invoice-03" class="icon"></iconify-icon>
                            </span>
                            <div>
                                <span class="mb-2 font-medium text-secondary-light text-sm">Order Bulan Ini</span>
                                <h6 class="font-semibold">{{ number_format($ordersThisMonth) }}</h6>
                            </div>
                        </div>
                        <div id="new-user-chart" class="remove-tooltip-title rounded-tooltip-value"></div>
                    </div>
                    <p class="text-sm mb-0">
                        @if($orderGrowth >= 0)
                            Naik <span class="bg-success-100 dark:bg-success-600/25 px-1 py-px rounded font-medium text-success-600 dark:text-success-400 text-sm">+{{ $orderGrowth }}%</span> bulan ini
                        @else
                            Turun <span class="bg-danger-100 dark:bg-danger-600/25 px-1 py-px rounded font-medium text-danger-600 dark:text-danger-400 text-sm">{{ $orderGrowth }}%</span> bulan ini
                        @endif
                    </p>
                </div>
            </div>

            {{-- Teknisi Aktif --}}
            <div class="card px-4 py-5 shadow-2 rounded-lg border-gray-200 dark:border-neutral-600 h-full bg-gradient-to-l from-success-600/10 to-bg-white">
                <div class="card-body p-0">
                    <div class="flex flex-wrap items-center justify-between gap-1 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="mb-0 w-[44px] h-[44px] bg-success-600 shrink-0 text-white flex justify-center items-center rounded-full h6">
                                <iconify-icon icon="solar:user-id-outline" class="icon"></iconify-icon>
                            </span>
                            <div>
                                <span class="mb-2 font-medium text-secondary-light text-sm">Teknisi Aktif</span>
                                <h6 class="font-semibold">{{ number_format($totalTechnicians) }}</h6>
                            </div>
                        </div>
                        <div id="active-user-chart" class="remove-tooltip-title rounded-tooltip-value"></div>
                    </div>
                    <p class="text-sm mb-0">Total customer: <span class="bg-success-100 dark:bg-success-600/25 px-1 py-px rounded font-medium text-success-600 dark:text-success-400 text-sm">{{ number_format($totalCustomers) }}</span></p>
                </div>
            </div>

            {{-- Total Pendapatan --}}
            <div class="card px-4 py-5 shadow-2 rounded-lg border-gray-200 dark:border-neutral-600 h-full bg-gradient-to-l from-warning-600/10 to-bg-white">
                <div class="card-body p-0">
                    <div class="flex flex-wrap items-center justify-between gap-1 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="mb-0 w-[44px] h-[44px] bg-warning-600 text-white shrink-0 flex justify-center items-center rounded-full h6">
                                <iconify-icon icon="hugeicons:money-send-square" class="icon"></iconify-icon>
                            </span>
                            <div>
                                <span class="mb-2 font-medium text-secondary-light text-sm">Revenue Bulan Ini</span>
                                <h6 class="font-semibold">Rp {{ number_format($revenueThisMonth, 0, ',', '.') }}</h6>
                            </div>
                        </div>
                        <div id="total-sales-chart" class="remove-tooltip-title rounded-tooltip-value"></div>
                    </div>
                    <p class="text-sm mb-0">
                        @if($revenueGrowth >= 0)
                            Naik <span class="bg-success-100 dark:bg-success-600/25 px-1 py-px rounded font-medium text-success-600 dark:text-success-400 text-sm">+{{ $revenueGrowth }}%</span> bulan ini
                        @else
                            Turun <span class="bg-danger-100 dark:bg-danger-600/25 px-1 py-px rounded font-medium text-danger-600 dark:text-danger-400 text-sm">{{ $revenueGrowth }}%</span> bulan ini
                        @endif
                    </p>
                </div>
            </div>

            {{-- Total Order Keseluruhan --}}
            <div class="card px-4 py-5 shadow-2 rounded-lg border-gray-200 dark:border-neutral-600 h-full bg-gradient-to-l from-purple-600/10 to-bg-white">
                <div class="card-body p-0">
                    <div class="flex flex-wrap items-center justify-between gap-1 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="mb-0 w-[44px] h-[44px] bg-purple-600 text-white shrink-0 flex justify-center items-center rounded-full h6">
                                <iconify-icon icon="mingcute:user-follow-fill" class="icon"></iconify-icon>
                            </span>
                            <div>
                                <span class="mb-2 font-medium text-secondary-light text-sm">Total Order</span>
                                <h6 class="font-semibold">{{ number_format($totalOrders) }}</h6>
                            </div>
                        </div>
                        <div id="conversion-user-chart" class="remove-tooltip-title rounded-tooltip-value"></div>
                    </div>
                    <p class="text-sm mb-0">Total revenue: <span class="bg-success-100 dark:bg-success-600/25 px-1 py-px rounded font-medium text-success-600 dark:text-success-400 text-sm">Rp {{ number_format($totalRevenue / 1000000, 1) }}jt</span></p>
                </div>
            </div>

            {{-- Komplain --}}
            <div class="card px-4 py-5 shadow-2 rounded-lg border-gray-200 dark:border-neutral-600 h-full bg-gradient-to-l from-pink-600/10 to-bg-white">
                <div class="card-body p-0">
                    <div class="flex flex-wrap items-center justify-between gap-1 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="mb-0 w-[44px] h-[44px] bg-pink-600 text-white shrink-0 flex justify-center items-center rounded-full h6">
                                <iconify-icon icon="mage:message-question-mark-round" class="icon"></iconify-icon>
                            </span>
                            <div>
                                <span class="mb-2 font-medium text-secondary-light text-sm">Komplain Aktif</span>
                                <h6 class="font-semibold">{{ $openComplaints }}</h6>
                            </div>
                        </div>
                        <div id="leads-chart" class="remove-tooltip-title rounded-tooltip-value"></div>
                    </div>
                    <p class="text-sm mb-0">
                        @if($openComplaints > 0)
                            <span class="bg-danger-100 dark:bg-danger-600/25 px-1 py-px rounded font-medium text-danger-600 dark:text-danger-400 text-sm">{{ $openComplaints }} perlu ditangani</span>
                        @else
                            <span class="bg-success-100 dark:bg-success-600/25 px-1 py-px rounded font-medium text-success-600 dark:text-success-400 text-sm">Semua tertangani</span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- Withdrawal Pending --}}
            <div class="card px-4 py-5 shadow-2 rounded-lg border-gray-200 dark:border-neutral-600 h-full bg-gradient-to-l from-cyan-600/10 to-bg-white">
                <div class="card-body p-0">
                    <div class="flex flex-wrap items-center justify-between gap-1 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="mb-0 w-[44px] h-[44px] bg-cyan-600 text-white shrink-0 flex justify-center items-center rounded-full h6">
                                <iconify-icon icon="solar:card-transfer-outline" class="icon"></iconify-icon>
                            </span>
                            <div>
                                <span class="mb-2 font-medium text-secondary-light text-sm">Withdrawal Pending</span>
                                <h6 class="font-semibold">{{ $pendingWithdrawals }}</h6>
                            </div>
                        </div>
                        <div id="total-profit-chart" class="remove-tooltip-title rounded-tooltip-value"></div>
                    </div>
                    <p class="text-sm mb-0">
                        @if($pendingWithdrawals > 0)
                            <span class="bg-warning-100 dark:bg-warning-600/25 px-1 py-px rounded font-medium text-warning-600 dark:text-warning-400 text-sm">{{ $pendingWithdrawals }} menunggu persetujuan</span>
                        @else
                            <span class="bg-success-100 dark:bg-success-600/25 px-1 py-px rounded font-medium text-success-600 dark:text-success-400 text-sm">Tidak ada pending</span>
                        @endif
                    </p>
                </div>
            </div>

        </div>
    </div>

    {{-- Revenue Growth Chart --}}
    <div class="lg:col-span-12 2xl:col-span-4">
        <div class="card h-full rounded-lg border-0">
            <div class="card-body p-6">
                <div class="flex items-center flex-wrap gap-2 justify-between">
                    <div>
                        <h6 class="mb-2 font-bold text-lg">Pertumbuhan Pendapatan</h6>
                        <span class="text-sm font-medium text-secondary-light">Laporan 12 Bulan Terakhir</span>
                    </div>
                    <div class="text-end">
                        <h6 class="mb-2 font-bold text-lg">Rp {{ number_format($revenueThisMonth / 1000000, 1) }}jt</h6>
                        @if($revenueGrowth >= 0)
                            <span class="bg-success-100 dark:bg-success-600/25 px-3 py-1 rounded font-medium text-success-600 dark:text-success-400 text-sm">+{{ $revenueGrowth }}%</span>
                        @else
                            <span class="bg-danger-100 dark:bg-danger-600/25 px-3 py-1 rounded font-medium text-danger-600 dark:text-danger-400 text-sm">{{ $revenueGrowth }}%</span>
                        @endif
                    </div>
                </div>
                <div class="mt-4">
                    <canvas id="revenueChart" height="160"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Order Terbaru --}}
    <div class="lg:col-span-12 2xl:col-span-8">
        <div class="card h-full border-0 overflow-hidden">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6 flex items-center justify-between">
                <h6 class="text-lg font-semibold mb-0">Order Terbaru</h6>
                <a href="{{ route('orders.index') }}" class="text-primary-600 dark:text-primary-600 hover-text-primary flex items-center gap-1">
                    Lihat Semua
                    <iconify-icon icon="solar:alt-arrow-right-linear" class="icon"></iconify-icon>
                </a>
            </div>
            <div class="card-body p-6">
                <div class="table-responsive scroll-sm">
                    <table class="table bordered-table style-two mb-0">
                        <thead>
                            <tr>
                                <th>Kode Order</th>
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
                                    'pending'              => 'bg-warning-100 dark:bg-warning-600/25 text-warning-600',
                                    'confirmed'            => 'bg-info-100 dark:bg-info-600/25 text-info-600',
                                    'in_progress'          => 'bg-purple-100 dark:bg-purple-600/25 text-purple-600',
                                    'waiting_confirmation' => 'bg-info-100 dark:bg-info-600/25 text-info-600',
                                    'warranty'             => 'bg-success-100 dark:bg-success-600/25 text-success-600',
                                    'complained'           => 'bg-danger-100 dark:bg-danger-600/25 text-danger-600',
                                    'completed'            => 'bg-success-100 dark:bg-success-600/25 text-success-600',
                                    'cancelled'            => 'bg-neutral-100 dark:bg-neutral-600/25 text-neutral-600',
                                ];
                                $sc = $statusColors[$order->status] ?? 'bg-neutral-100 dark:bg-neutral-600/25 text-neutral-600';
                                $statusLabels = [
                                    'pending'              => 'Pending',
                                    'confirmed'            => 'Dikonfirmasi',
                                    'in_progress'          => 'Dalam Proses',
                                    'waiting_confirmation' => 'Menunggu Konfirmasi',
                                    'warranty'             => 'Garansi',
                                    'complained'           => 'Dikomplain',
                                    'completed'            => 'Selesai',
                                    'cancelled'            => 'Dibatalkan',
                                ];
                                $sl = $statusLabels[$order->status] ?? ucfirst($order->status);
                            @endphp
                            <tr>
                                <td class="text-primary-600 font-medium">#{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $order->user->name ?? '-' }}</td>
                                <td>{{ $order->businessPartner->name ?? '-' }}</td>
                                <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                <td>
                                    <span class="{{ $sc }} px-3 py-1 rounded-full text-sm font-medium">
                                        {{ $sl }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary-light py-6">Belum ada order.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Teknisi --}}
    <div class="lg:col-span-12 2xl:col-span-4">
        <div class="card border-0 overflow-hidden">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6 flex items-center justify-between">
                <h6 class="text-lg font-semibold mb-0">Top Teknisi Bulan Ini</h6>
                <a href="{{ route('technicians.index') }}" class="text-primary-600 hover-text-primary flex items-center gap-1">
                    Lihat Semua
                    <iconify-icon icon="solar:alt-arrow-right-linear" class="icon"></iconify-icon>
                </a>
            </div>
            <div class="card-body p-6">
                <div class="flex flex-col gap-5">
                    @forelse($topTechnicians as $i => $tech)
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0
                                {{ $i === 0 ? 'bg-warning-100 text-warning-600' : ($i === 1 ? 'bg-neutral-200 text-neutral-600' : 'bg-neutral-100 text-neutral-400') }}">
                                {{ $i + 1 }}
                            </span>
                            <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center font-bold text-primary-600 text-sm shrink-0">
                                {{ strtoupper(substr($tech->user->name ?? 'T', 0, 1)) }}
                            </div>
                            <div>
                                <h6 class="text-sm font-semibold mb-0">{{ $tech->user->name ?? '-' }}</h6>
                                <span class="text-xs text-secondary-light capitalize">Teknisi {{ ucfirst($tech->grade) }}</span>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="text-sm font-semibold block">{{ $tech->completed_this_month }} order</span>
                            <span class="text-xs text-warning-600">&#9733; {{ number_format($tech->avg_rating ?? 0, 1) }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-secondary-light text-sm py-4">Belum ada data teknisi.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Status Order Donut + Summary --}}
    <div class="lg:col-span-12 2xl:col-span-4">
        <div class="card border-0 overflow-hidden h-full">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6">
                <h6 class="text-lg font-semibold mb-0">Distribusi Status Order</h6>
            </div>
            <div class="card-body p-6 flex items-center justify-center">
                <canvas id="statusChart" style="max-height: 220px;"></canvas>
            </div>
        </div>
    </div>

    {{-- Revenue Chart Full Width Row --}}
    <div class="lg:col-span-12 2xl:col-span-8">
        <div class="card border-0 overflow-hidden h-full">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6">
                <h6 class="text-lg font-semibold mb-0">Revenue 12 Bulan Terakhir (Detail)</h6>
            </div>
            <div class="card-body p-6">
                <canvas id="revenueBarChart" height="110"></canvas>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const revenueLabels = {!! json_encode($revenueChart->pluck('month')) !!};
    const revenueData   = {!! json_encode($revenueChart->pluck('total')) !!};
    const statusData    = {!! json_encode($orderStatus) !!};

    // Revenue Line Chart (in sidebar card)
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: revenueLabels,
            datasets: [{
                label: 'Revenue',
                data: revenueData,
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
                        callback: v => 'Rp ' + (v / 1000000).toFixed(1) + 'jt'
                    }
                }
            }
        }
    });

    // Revenue Bar Chart (full width)
    const revenueBarCtx = document.getElementById('revenueBarChart').getContext('2d');
    new Chart(revenueBarCtx, {
        type: 'bar',
        data: {
            labels: revenueLabels,
            datasets: [{
                label: 'Revenue',
                data: revenueData,
                backgroundColor: 'rgba(25, 118, 210, 0.75)',
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: v => 'Rp ' + (v / 1000000).toFixed(1) + 'jt'
                    }
                }
            }
        }
    });

    // Status Donut Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusLabels = Object.keys(statusData);
    const statusValues = Object.values(statusData);
    const statusLabelMap = {
        pending: 'Pending',
        confirmed: 'Dikonfirmasi',
        in_progress: 'Dalam Proses',
        waiting_confirmation: 'Menunggu Konfirmasi',
        warranty: 'Garansi',
        complained: 'Dikomplain',
        completed: 'Selesai',
        cancelled: 'Dibatalkan',
    };
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusLabels.map(k => statusLabelMap[k] ?? k),
            datasets: [{
                data: statusValues,
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
                legend: {
                    position: 'bottom',
                    labels: { font: { size: 11 }, padding: 10 }
                }
            }
        }
    });
</script>
@endpush