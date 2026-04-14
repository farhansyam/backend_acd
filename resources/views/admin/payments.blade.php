@extends('layouts.app')
@section('title', 'Pembayaran')
@section('page-title', 'Pembayaran')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">Pembayaran</li>
@endsection

@section('content')
<div class="card border-0 mt-6">
    <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6 flex items-center justify-between">
        <h6 class="text-lg font-semibold mb-0">Riwayat Pembayaran</h6>
        <span class="text-sm text-secondary-light">Total: {{ $orders->total() }} transaksi</span>
    </div>
    <div class="card-body p-6">
        <div class="table-responsive">
            <table class="table bordered-table style-two mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Mitra</th>
                        <th>Metode</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Dibayar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    @php
                        $statusMap = [
                            'paid'    => ['class' => 'bg-success-100 text-success-600', 'label' => 'Lunas'],
                            'expired' => ['class' => 'bg-warning-100 text-warning-600', 'label' => 'Kadaluarsa'],
                            'failed'  => ['class' => 'bg-danger-100 text-danger-600',  'label' => 'Gagal'],
                        ];
                        $s = $statusMap[$order->payment_status] ?? ['class' => 'bg-neutral-100 text-neutral-600', 'label' => $order->payment_status];
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="font-medium">#{{ $order->id }}</td>
                        <td>{{ $order->user->name ?? '-' }}</td>
                        <td>{{ $order->businessPartner->name ?? '-' }}</td>
                        <td>{{ $order->payment_method ?? '-' }}</td>
                        <td class="font-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        <td>
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $s['class'] }}">
                                {{ $s['label'] }}
                            </span>
                        </td>
                        <td class="text-sm text-secondary-light">
                            {{ $order->paid_at ? \Carbon\Carbon::parse($order->paid_at)->format('d M Y H:i') : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-secondary-light py-6">Belum ada data pembayaran.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $orders->links() }}</div>
    </div>
</div>
@endsection