@extends('layouts.app')
@section('title', 'Dashboard BP')
@section('page-title', 'Dashboard')

@section('content')

{{-- Welcome --}}
<div class="bg-gradient-to-r from-primary-600 to-primary-400 rounded-2xl p-6 mt-6 mb-6 text-white">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm opacity-80 mb-1">Selamat datang,</p>
            <h4 class="text-2xl font-bold">{{ $bp->name }}</h4>
            <p class="text-sm opacity-80 mt-1">{{ $bp->city ?? '-' }}</p>
        </div>
        <div class="text-right">
            <p class="text-xs opacity-80 mb-1">SALDO BP</p>
            <p class="text-2xl font-bold">Rp {{ number_format($bpBalance, 0, ',', '.') }}</p>
        </div>
    </div>
</div>

{{-- Summary Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <div class="card border-0">
        <div class="card-body p-5">
            <div class="w-10 h-10 bg-primary-100 rounded-xl flex items-center justify-center mb-3">
                <iconify-icon icon="hugeicons:invoice-03" class="text-primary-600 text-xl"></iconify-icon>
            </div>
            <p class="text-2xl font-bold mb-1">{{ $ordersThisMonth }}</p>
            <p class="text-sm text-secondary-light">Order Bulan Ini</p>
            @if($orderGrowth != 0)
            <p class="text-xs mt-1 {{ $orderGrowth >= 0 ? 'text-success-600' : 'text-danger-600' }} font-medium">
                {{ $orderGrowth >= 0 ? '↑' : '↓' }} {{ abs($orderGrowth) }}% vs bulan lalu
            </p>
            @endif
        </div>
    </div>

    <div class="card border-0">
        <div class="card-body p-5">
            <div class="w-10 h-10 bg-success-100 rounded-xl flex items-center justify-center mb-3">
                <iconify-icon icon="hugeicons:money-send-square" class="text-success-600 text-xl"></iconify-icon>
            </div>
            <p class="text-xl font-bold mb-1">Rp {{ number_format($revenueThisMonth, 0, ',', '.') }}</p>
            <p class="text-sm text-secondary-light">Revenue Bulan Ini</p>
        </div>
    </div>

    <div class="card border-0">
        <div class="card-body p-5">
            <div class="w-10 h-10 bg-warning-100 rounded-xl flex items-center justify-center mb-3">
                <iconify-icon icon="lucide:clock" class="text-warning-600 text-xl"></iconify-icon>
            </div>
            <p class="text-2xl font-bold mb-1">{{ $pendingOrders }}</p>
            <p class="text-sm text-secondary-light">Order Perlu Assign</p>
            @if($pendingOrders > 0)
            <a href="{{ route('orders.index') }}" class="text-xs text-primary-600 font-medium mt-1 block">Assign sekarang →</a>
            @endif
        </div>
    </div>

    <div class="card border-0">
        <div class="card-body p-5">
            <div class="w-10 h-10 bg-danger-100 rounded-xl flex items-center justify-center mb-3">
                <iconify-icon icon="mage:message-question-mark-round" class="text-danger-600 text-xl"></iconify-icon>
            </div>
            <p class="text-2xl font-bold mb-1">{{ $activeComplaints }}</p>
            <p class="text-sm text-secondary-light">Komplain Aktif</p>
            @if($activeComplaints > 0)
            <a href="{{ route('complaints.index') }}" class="text-xs text-danger-600 font-medium mt-1 block">Tangani sekarang →</a>
            @endif
        </div>
    </div>

</div>

<div class="grid grid-cols-12 gap-6">

    {{-- Order Chart --}}
    <div class="col-span-12 lg:col-span-7">
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-4 px-6">
                <h6 class="font-semibold mb-0">Order 6 Bulan Terakhir</h6>
            </div>
            <div class="card-body p-6">
                <canvas id="orderChart" height="150"></canvas>
            </div>
        </div>
    </div>

    {{-- Top Teknisi --}}
    <div class="col-span-12 lg:col-span-5">
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-4 px-6 flex items-center justify-between">
                <h6 class="font-semibold mb-0">Performa Teknisi</h6>
                <span class="text-xs text-secondary-light">{{ $myTechnicians }} teknisi aktif</span>
            </div>
            <div class="card-body p-0">
                <ul class="divide-y divide-neutral-100 dark:divide-neutral-700">
                    @forelse($topTechnicians as $i => $tech)
                    <li class="flex items-center gap-3 px-6 py-4">
                        <div class="w-9 h-9 rounded-full bg-primary-100 flex items-center justify-center font-bold text-primary-600 text-sm">
                            {{ strtoupper(substr($tech->user->name ?? 'T', 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-sm">{{ $tech->user->name ?? '-' }}</p>
                            <div class="flex items-center gap-2">
                                <span class="text-xs capitalize text-secondary-light">{{ $tech->grade }}</span>
                                @if($tech->avg_rating > 0)
                                <span class="text-xs text-warning-600">⭐ {{ number_format($tech->avg_rating, 1) }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-sm text-primary-600">{{ $tech->completed_this_month }}</p>
                            <p class="text-xs text-secondary-light">order</p>
                        </div>
                    </li>
                    @empty
                    <li class="px-6 py-8 text-center text-secondary-light text-sm">Belum ada teknisi.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    {{-- Order Terbaru --}}
    <div class="col-span-12">
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
                                <td>{{ $order->technician?->user?->name ?? '-' }}</td>
                                <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                <td>
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $sc }}">
                                        {{ $order->status }}
                                    </span>
                                </td>
                                <td class="text-sm text-secondary-light">
                                    {{ $order->created_at->format('d M Y') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary-light py-4">Belum ada order.</td>
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
const orderCtx = document.getElementById('orderChart').getContext('2d');
new Chart(orderCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($orderChart->pluck('month')) !!},
        datasets: [{
            label: 'Jumlah Order',
            data: {!! json_encode($orderChart->pluck('count')) !!},
            backgroundColor: 'rgba(25, 118, 210, 0.8)',
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
@endsection