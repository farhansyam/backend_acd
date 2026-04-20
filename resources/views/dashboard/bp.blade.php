@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">Business Partner</li>
@endsection

@section('content')

{{-- Welcome Banner --}}
<div class="bg-gradient-to-r from-primary-600 to-primary-400 rounded-2xl px-6 py-5 mt-6 mb-6 text-white">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <span class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center text-2xl font-bold">
                {{ strtoupper(substr($bp->name, 0, 1)) }}
            </span>
            <div>
                <p class="text-sm opacity-80 mb-0.5">Selamat datang,</p>
                <h5 class="text-xl font-bold mb-0">{{ $bp->name }}</h5>
                <p class="text-sm opacity-70 mt-0.5">
                    <iconify-icon icon="mingcute:location-line" class="inline-block mr-1"></iconify-icon>
                    {{ $bp->city ?? '-' }}
                </p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-xs opacity-80 mb-1 uppercase tracking-wider">Saldo BP</p>
            <p class="text-2xl font-bold">Rp {{ number_format($bpBalance, 0, ',', '.') }}</p>
            <a href="{{ route('withdrawals.index') }}" class="text-xs bg-white/20 hover:bg-white/30 px-3 py-1 rounded-full mt-1 inline-block transition-all">
                Tarik Saldo →
            </a>
        </div>
    </div>
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
    <div class="lg:col-span-12">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">

            {{-- Order Bulan Ini --}}
            <div class="card px-4 py-5 shadow-2 rounded-lg border-gray-200 dark:border-neutral-600 h-full bg-gradient-to-l from-primary-600/10 to-bg-white">
                <div class="card-body p-0">
                    <div class="flex flex-wrap items-center justify-between gap-1 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="mb-0 w-[44px] h-[44px] bg-primary-600 shrink-0 text-white flex justify-center items-center rounded-full">
                                <iconify-icon icon="hugeicons:invoice-03" class="icon"></iconify-icon>
                            </span>
                            <div>
                                <span class="mb-2 font-medium text-secondary-light text-sm">Order Bulan Ini</span>
                                <h6 class="font-semibold">{{ number_format($ordersThisMonth) }}</h6>
                            </div>
                        </div>
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

            {{-- Revenue Bulan Ini --}}
            <div class="card px-4 py-5 shadow-2 rounded-lg border-gray-200 dark:border-neutral-600 h-full bg-gradient-to-l from-success-600/10 to-bg-white">
                <div class="card-body p-0">
                    <div class="flex flex-wrap items-center justify-between gap-1 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="mb-0 w-[44px] h-[44px] bg-success-600 shrink-0 text-white flex justify-center items-center rounded-full">
                                <iconify-icon icon="hugeicons:money-send-square" class="icon"></iconify-icon>
                            </span>
                            <div>
                                <span class="mb-2 font-medium text-secondary-light text-sm">Revenue Bulan Ini</span>
                                <h6 class="font-semibold">Rp {{ number_format($revenueThisMonth / 1000000, 1) }}jt</h6>
                            </div>
                        </div>
                    </div>
                    <p class="text-sm mb-0">
                        <span class="text-secondary-light">Total: </span>
                        <span class="font-medium">Rp {{ number_format($revenueThisMonth, 0, ',', '.') }}</span>
                    </p>
                </div>
            </div>

            {{-- Order Perlu Assign --}}
            <div class="card px-4 py-5 shadow-2 rounded-lg border-gray-200 dark:border-neutral-600 h-full bg-gradient-to-l from-warning-600/10 to-bg-white">
                <div class="card-body p-0">
                    <div class="flex flex-wrap items-center justify-between gap-1 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="mb-0 w-[44px] h-[44px] bg-warning-600 text-white shrink-0 flex justify-center items-center rounded-full">
                                <iconify-icon icon="lucide:clock" class="icon"></iconify-icon>
                            </span>
                            <div>
                                <span class="mb-2 font-medium text-secondary-light text-sm">Perlu Assign</span>
                                <h6 class="font-semibold">{{ $pendingOrders }}</h6>
                            </div>
                        </div>
                    </div>
                    <p class="text-sm mb-0">
                        @if($pendingOrders > 0)
                            <a href="{{ route('orders.index') }}" class="bg-warning-100 dark:bg-warning-600/25 px-1 py-px rounded font-medium text-warning-600 dark:text-warning-400 text-sm">Assign sekarang →</a>
                        @else
                            <span class="bg-success-100 dark:bg-success-600/25 px-1 py-px rounded font-medium text-success-600 dark:text-success-400 text-sm">Semua terassign</span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- Komplain Aktif --}}
            <div class="card px-4 py-5 shadow-2 rounded-lg border-gray-200 dark:border-neutral-600 h-full bg-gradient-to-l from-pink-600/10 to-bg-white">
                <div class="card-body p-0">
                    <div class="flex flex-wrap items-center justify-between gap-1 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="mb-0 w-[44px] h-[44px] bg-pink-600 text-white shrink-0 flex justify-center items-center rounded-full">
                                <iconify-icon icon="mage:message-question-mark-round" class="icon"></iconify-icon>
                            </span>
                            <div>
                                <span class="mb-2 font-medium text-secondary-light text-sm">Komplain Aktif</span>
                                <h6 class="font-semibold">{{ $activeComplaints }}</h6>
                            </div>
                        </div>
                    </div>
                    <p class="text-sm mb-0">
                        @if($activeComplaints > 0)
                            <a href="{{ route('complaints.index') }}" class="bg-danger-100 dark:bg-danger-600/25 px-1 py-px rounded font-medium text-danger-600 dark:text-danger-400 text-sm">Tangani sekarang →</a>
                        @else
                            <span class="bg-success-100 dark:bg-success-600/25 px-1 py-px rounded font-medium text-success-600 dark:text-success-400 text-sm">Semua tertangani</span>
                        @endif
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

    {{-- Order Chart --}}
    <div class="lg:col-span-12 2xl:col-span-8">
        <div class="card h-full rounded-lg border-0">
            <div class="card-body p-6">
                <div class="flex items-center flex-wrap gap-2 justify-between mb-4">
                    <div>
                        <h6 class="mb-2 font-bold text-lg">Tren Order</h6>
                        <span class="text-sm font-medium text-secondary-light">6 Bulan Terakhir</span>
                    </div>
                    <div class="text-end">
                        <h6 class="mb-2 font-bold text-lg">{{ number_format($ordersThisMonth) }} order</h6>
                        @if($orderGrowth >= 0)
                            <span class="bg-success-100 dark:bg-success-600/25 px-3 py-1 rounded font-medium text-success-600 dark:text-success-400 text-sm">+{{ $orderGrowth }}%</span>
                        @else
                            <span class="bg-danger-100 dark:bg-danger-600/25 px-3 py-1 rounded font-medium text-danger-600 dark:text-danger-400 text-sm">{{ $orderGrowth }}%</span>
                        @endif
                    </div>
                </div>
                <canvas id="orderChart" height="130"></canvas>
            </div>
        </div>
    </div>

    {{-- Performa Teknisi --}}
    <div class="lg:col-span-12 2xl:col-span-4">
        <div class="card border-0 overflow-hidden h-full">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6 flex items-center justify-between">
                <h6 class="text-lg font-semibold mb-0">Performa Teknisi</h6>
                <span class="text-xs bg-success-100 dark:bg-success-600/25 text-success-600 dark:text-success-400 px-2 py-1 rounded-full font-medium">{{ $myTechnicians }} aktif</span>
            </div>
            <div class="card-body p-6">
                <div class="flex flex-col gap-5">
                    @forelse($topTechnicians as $i => $tech)
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0
                                {{ $i === 0 ? 'bg-warning-100 text-warning-600' : ($i === 1 ? 'bg-neutral-200 dark:bg-neutral-600 text-neutral-600 dark:text-neutral-300' : 'bg-neutral-100 dark:bg-neutral-700 text-neutral-400') }}">
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
                            @if(($tech->avg_rating ?? 0) > 0)
                                <span class="text-xs text-warning-600">&#9733; {{ number_format($tech->avg_rating, 1) }}</span>
                            @else
                                <span class="text-xs text-secondary-light">Belum ada rating</span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-secondary-light text-sm py-4">Belum ada teknisi.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Order Terbaru --}}
    <div class="lg:col-span-12">
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
                                <th>Teknisi</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Tanggal</th>
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
                                <td>{{ $order->technician?->user?->name ?? '<span class="text-secondary-light italic text-xs">Belum diassign</span>' }}</td>
                                <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                <td>
                                    <span class="{{ $sc }} px-3 py-1 rounded-full text-sm font-medium">
                                        {{ $sl }}
                                    </span>
                                </td>
                                <td class="text-sm text-secondary-light">{{ $order->created_at->format('d M Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary-light py-6">Belum ada order.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const orderCtx = document.getElementById('orderChart').getContext('2d');
new Chart(orderCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($orderChart->pluck('month')) !!},
        datasets: [{
            label: 'Jumlah Order',
            data: {!! json_encode($orderChart->pluck('count')) !!},
            backgroundColor: 'rgba(25, 118, 210, 0.75)',
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});
</script>
@endpush