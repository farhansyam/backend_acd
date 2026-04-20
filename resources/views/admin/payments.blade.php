@extends('layouts.app')
@section('title', 'Pembayaran')
@section('page-title', 'Pembayaran')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">Pembayaran</li>
@endsection

@section('content')

{{-- Summary --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 mb-6">
    <div class="card border-0">
        <div class="card-body p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-success-100 rounded-xl flex items-center justify-center">
                <iconify-icon icon="hugeicons:money-send-square" class="text-success-600 text-2xl"></iconify-icon>
            </div>
            <div>
                <p class="text-sm text-secondary-light mb-1">Total Transaksi</p>
                <p class="text-xl font-bold text-success-600">{{ $orders->total() }} transaksi</p>
            </div>
        </div>
    </div>
    <div class="card border-0">
        <div class="card-body p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-danger-100 rounded-xl flex items-center justify-center">
                <iconify-icon icon="lucide:wrench" class="text-danger-600 text-2xl"></iconify-icon>
            </div>
            <div>
                <p class="text-sm text-secondary-light mb-1">Total Biaya Rework</p>

                <p class="text-xl font-bold text-danger-600">Rp {{ number_format($totalReworkCost, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
    <div class="card border-0">
        <div class="card-body p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-warning-100 rounded-xl flex items-center justify-center">
                <iconify-icon icon="lucide:alert-triangle" class="text-warning-600 text-2xl"></iconify-icon>
            </div>
            <div>
                <p class="text-sm text-secondary-light mb-1">Kasus Rework</p>
                <span class="text-sm text-secondary-light">Total: {{ $reworkCosts->count() }} kasus</span>
            </div>
        </div>
    </div>
</div>

{{-- Tab --}}
<div class="flex gap-2 mb-4">
    <button onclick="showTab('payments')" id="tab-payments"
        class="px-4 py-2 rounded-lg text-sm font-medium bg-primary-600 text-white tab-btn">
        Riwayat Pembayaran
    </button>
    <button onclick="showTab('rework')" id="tab-rework"
        class="px-4 py-2 rounded-lg text-sm font-medium bg-neutral-100 text-neutral-600 tab-btn">
        Biaya Rework
    </button>
</div>

{{-- ===== Tab Pembayaran ===== --}}
<div id="section-payments">
    <div class="card border-0">
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
</div>

{{-- ===== Tab Biaya Rework ===== --}}
<div id="section-rework" style="display:none">
    <div class="card border-0">
        <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6 flex items-center justify-between">
            <h6 class="text-lg font-semibold mb-0">Biaya Rework dari Admin</h6>
            <span class="text-sm text-secondary-light">Total: {{ $reworkCosts->count() }} kasus</span>
        </div>
        <div class="card-body p-6">
            <div class="bg-danger-50 border border-danger-200 rounded-lg px-4 py-3 mb-4 flex items-center gap-2">
                <iconify-icon icon="lucide:info" class="text-danger-600"></iconify-icon>
                <span class="text-sm text-danger-700">
                    Biaya rework dikeluarkan admin untuk membayar teknisi rework yang berbeda dari teknisi pertama.
                    Total pengeluaran: <strong>Rp {{ number_format($totalReworkCost, 0, ',', '.') }}</strong>
                </span>
            </div>
            <div class="table-responsive">
                <table class="table bordered-table style-two mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Komplain</th>
                            <th>Order</th>
                            <th>Teknisi Rework</th>
                            <th>Grade</th>
                            <th>Biaya Rework</th>
                            <th>Diselesaikan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reworkCosts as $complaint)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('complaints.show', $complaint) }}"
                                   class="text-primary-600 font-medium hover:underline">
                                    #{{ $complaint->id }}
                                </a>
                                <p class="text-xs text-secondary-light mt-0.5">{{ Str::limit($complaint->title, 30) }}</p>
                            </td>
                            <td class="font-medium">#{{ $complaint->order_id }}</td>
                            <td>
                                <p class="font-medium text-sm">{{ $complaint->reworkTechnician?->user?->name ?? '-' }}</p>
                            </td>
                            <td>
                                <span class="px-2 py-1 rounded-full text-xs font-medium capitalize
                                    {{ match($complaint->reworkTechnician?->grade) {
                                        'pro'    => 'bg-success-100 text-success-600',
                                        'medium' => 'bg-warning-100 text-warning-600',
                                        default  => 'bg-info-100 text-info-600',
                                    } }}">
                                    {{ $complaint->reworkTechnician?->grade ?? '-' }}
                                </span>
                            </td>
                            <td class="font-bold text-danger-600">
                                Rp {{ number_format($complaint->rework_cost, 0, ',', '.') }}
                            </td>
                            <td class="text-sm text-secondary-light">
                                {{ $complaint->resolved_at?->format('d M Y H:i') ?? '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary-light py-6">Belum ada biaya rework.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showTab(tab) {
    document.getElementById('section-payments').style.display = tab === 'payments' ? '' : 'none';
    document.getElementById('section-rework').style.display   = tab === 'rework'   ? '' : 'none';

    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('bg-primary-600', 'text-white');
        btn.classList.add('bg-neutral-100', 'text-neutral-600');
    });
    document.getElementById('tab-' + tab).classList.remove('bg-neutral-100', 'text-neutral-600');
    document.getElementById('tab-' + tab).classList.add('bg-primary-600', 'text-white');
}
</script>
@endpush

@endsection