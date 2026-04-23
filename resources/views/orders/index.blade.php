@extends('layouts.app')
@section('title', 'Manajemen Order')
@section('page-title', 'Manajemen Order')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">Order</li>
@endsection

@section('content')
<div class="card border-0 mt-6">
    <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6 flex items-center justify-between">
        <h6 class="text-lg font-semibold mb-0">Daftar Order</h6>
        <span class="text-sm text-secondary-light">Total: {{ $orders->total() }} order</span>
    </div>
    <div class="card-body p-6">

        @if(session('success'))
            <div class="bg-success-100 text-success-600 px-4 py-3 rounded mb-4 flex items-center gap-2">
                <iconify-icon icon="lucide:check-circle"></iconify-icon>
                {{ session('success') }}
            </div>
        @endif

        <div class="table-responsive">
            <table class="table bordered-table style-two mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        @if(auth()->user()->role === 'adminsuper')
                            <th>Mitra</th>
                        @endif
                        <th>Customer</th>
                        <th>Layanan</th>
                        <th>Jadwal</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Teknisi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    @php
                        $statusMap = [
                            'pending'                   => ['label' => 'Menunggu Konfirmasi',         'class' => 'bg-warning-100 text-warning-600'],
                            'pending_transport_fee'     => ['label' => 'Menunggu Biaya Transport',    'class' => 'bg-orange-100 text-orange-600'],
                            'pending_transport_fee_set' => ['label' => 'Konfirmasi Customer',         'class' => 'bg-purple-100 text-purple-600'],
                            'confirmed'                 => ['label' => 'Dikonfirmasi',                'class' => 'bg-info-100 text-info-600'],
                            'in_progress'               => ['label' => 'Dikerjakan',                  'class' => 'bg-warning-100 text-warning-600'],
                            'survey_in_progress'        => ['label' => 'Survei Berlangsung',          'class' => 'bg-indigo-100 text-indigo-600'],
                            'waiting_customer_response' => ['label' => 'Menunggu Keputusan Customer', 'class' => 'bg-purple-100 text-purple-600'],
                            'waiting_confirmation'      => ['label' => 'Menunggu Konfirmasi',         'class' => 'bg-purple-100 text-purple-600'],
                            'completed'                 => ['label' => 'Selesai',                     'class' => 'bg-success-100 text-success-600'],
                            'warranty'                  => ['label' => 'Masa Garansi',                'class' => 'bg-teal-100 text-teal-600'],
                            'complained'                => ['label' => 'Dikomplain',                  'class' => 'bg-danger-100 text-danger-600'],
                            'cancelled'                 => ['label' => 'Dibatalkan',                  'class' => 'bg-neutral-100 text-neutral-500'],
                        ];
                        $s = $statusMap[$order->status] ?? ['label' => $order->status, 'class' => 'bg-neutral-100 text-neutral-600'];
                    @endphp
                    <tr>
                        <td>#{{ $order->id }}</td>
                        @if(auth()->user()->role === 'adminsuper')
                            <td>{{ $order->businessPartner->name ?? '-' }}</td>
                        @endif
                        <td>{{ $order->user->name ?? '-' }}</td>
                        <td>
                            {{-- Badge perbaikan --}}
                            @if($order->is_perbaikan)
                                <span class="px-2 py-0.5 rounded text-xs font-bold bg-purple-100 text-purple-700 mb-1 inline-block">
                                    🔧 Perbaikan — {{ $order->perbaikan_phase === 'phase2' ? 'Fase 2' : 'Survey' }}
                                </span><br>
                            @endif
                            @foreach($order->items->take(2) as $item)
                                <span class="text-sm">{{ $item->bpService?->serviceType?->name }} x{{ $item->quantity }}</span><br>
                            @endforeach
                            @if($order->items->count() > 2)
                                <span class="text-xs text-secondary-light">+{{ $order->items->count() - 2 }} lainnya</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-sm">{{ $order->scheduled_date?->format('d M Y') }}</span><br>
                            <span class="text-xs text-secondary-light">{{ $order->scheduled_time }}</span>
                        </td>
                        <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        <td>
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $s['class'] }}">
                                {{ $s['label'] }}
                            </span>
                        </td>
                        <td>
                            @if($order->technician)
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 bg-primary-100 rounded-full flex items-center justify-center">
                                        <iconify-icon icon="lucide:user" class="text-primary-600 text-xs"></iconify-icon>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium">{{ $order->technician->user->name }}</p>
                                        <p class="text-xs text-secondary-light">{{ ucfirst($order->technician->grade) }}</p>
                                    </div>
                                </div>
                            @else
                                <span class="text-xs text-danger-600 font-medium">Belum di-assign</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('orders.show', $order) }}"
                               class="w-8 h-8 bg-primary-100 text-primary-600 rounded flex items-center justify-center">
                                <iconify-icon icon="lucide:eye"></iconify-icon>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-secondary-light py-6">Belum ada order.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>

    </div>
</div>
@endsection