@extends('layouts.app')
@section('title', 'Cuci Langganan')
@section('page-title', 'Cuci Langganan')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">Cuci Langganan</li>
@endsection

@section('content')
<div class="card border-0 mt-6">
    <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6 flex items-center justify-between">
        <h6 class="text-lg font-semibold mb-0">Daftar Langganan</h6>
        <span class="text-sm text-secondary-light">Total: {{ $subscriptions->total() }} langganan</span>
    </div>
    <div class="card-body p-6">

        @if(session('success'))
            <div class="bg-success-100 text-success-600 px-4 py-3 rounded mb-4 flex items-center gap-2">
                <iconify-icon icon="lucide:check-circle"></iconify-icon>
                {{ session('success') }}
            </div>
        @endif

        {{-- Filter --}}
        <form method="GET" action="{{ route('subscriptions.index') }}"
              class="flex flex-wrap gap-3 mb-6 items-end">
            <div>
                <label class="block text-xs text-secondary-light mb-1">Status</label>
                <select name="status" class="form-control radius-8 text-sm py-1.5">
                    <option value="">Semua Status</option>
                    <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
                    <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Aktif</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-secondary-light mb-1">Pembayaran</label>
                <select name="payment_status" class="form-control radius-8 text-sm py-1.5">
                    <option value="">Semua</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                    <option value="paid"   {{ request('payment_status') === 'paid'   ? 'selected' : '' }}>Lunas</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-secondary-light mb-1">Paket</label>
                <select name="package_type" class="form-control radius-8 text-sm py-1.5">
                    <option value="">Semua Paket</option>
                    <option value="hemat"    {{ request('package_type') === 'hemat'    ? 'selected' : '' }}>Paket Hemat</option>
                    <option value="rutin"    {{ request('package_type') === 'rutin'    ? 'selected' : '' }}>Paket Rutin</option>
                    <option value="intensif" {{ request('package_type') === 'intensif' ? 'selected' : '' }}>Paket Intensif</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary-600 flex items-center gap-2 text-sm py-2">
                    <iconify-icon icon="lucide:filter"></iconify-icon> Filter
                </button>
                <a href="{{ route('subscriptions.index') }}" class="btn btn-neutral-200 text-sm py-2">Reset</a>
            </div>
        </form>

        {{-- Tabel --}}
        <div class="table-responsive">
            <table class="table bordered-table style-two mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        @if(auth()->user()->role === 'adminsuper')
                            <th>Mitra</th>
                        @endif
                        <th>Customer</th>
                        <th>Paket</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th>Sesi</th>
                        <th>Masa Aktif</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $sub)
                    @php
                        $completedSessions = $sub->sessions->where('status', 'completed')->count();
                        $totalSessions     = $sub->package->total_sessions ?? 0;

                        $statusMap = [
                            'pending'   => ['label' => 'Pending',    'class' => 'bg-warning-100 text-warning-600'],
                            'active'    => ['label' => 'Aktif',      'class' => 'bg-info-100 text-info-600'],
                            'completed' => ['label' => 'Selesai',    'class' => 'bg-success-100 text-success-600'],
                            'cancelled' => ['label' => 'Dibatalkan', 'class' => 'bg-neutral-100 text-neutral-500'],
                        ];
                        $s = $statusMap[$sub->status] ?? ['label' => $sub->status, 'class' => 'bg-neutral-100 text-neutral-600'];

                        $packageClass = match($sub->package->type) {
                            'hemat'    => 'bg-info-100 text-info-600',
                            'rutin'    => 'bg-warning-100 text-warning-600',
                            'intensif' => 'bg-danger-100 text-danger-600',
                            default    => 'bg-neutral-100 text-neutral-600',
                        };
                    @endphp
                    <tr>
                        <td>#{{ $sub->id }}</td>
                        @if(auth()->user()->role === 'adminsuper')
                            <td>{{ $sub->businessPartner->name ?? '-' }}</td>
                        @endif
                        <td>
                            <p class="font-medium text-sm">{{ $sub->user->name }}</p>
                            <p class="text-xs text-secondary-light">{{ $sub->user->email }}</p>
                        </td>
                        <td>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $packageClass }}">
                                {{ $sub->package->name }}
                            </span>
                            <p class="text-xs text-secondary-light mt-1">
                                {{ $sub->package->total_sessions }}x / {{ $sub->package->interval_months }} bln sekali
                            </p>
                        </td>
                        <td class="font-semibold text-sm">
                            Rp {{ number_format($sub->total_amount, 0, ',', '.') }}
                        </td>
                        <td>
                            @if($sub->payment_status === 'paid')
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-success-100 text-success-600">✓ Lunas</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-neutral-100 text-neutral-500">Belum Bayar</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-16 bg-neutral-200 rounded-full" style="height:5px;">
                                    <div class="bg-primary-600 rounded-full" style="height:5px; width:{{ $totalSessions > 0 ? ($completedSessions / $totalSessions * 100) : 0 }}%"></div>
                                </div>
                                <span class="text-xs text-secondary-light">{{ $completedSessions }}/{{ $totalSessions }}</span>
                            </div>
                        </td>
                        <td>
                            @if($sub->starts_at && $sub->expires_at)
                                <p class="text-sm">{{ $sub->starts_at->format('d M Y') }}</p>
                                <p class="text-xs text-secondary-light">s/d {{ $sub->expires_at->format('d M Y') }}</p>
                            @else
                                <span class="text-xs text-secondary-light">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $s['class'] }}">{{ $s['label'] }}</span>
                        </td>
                        <td>
                            <a href="{{ route('subscriptions.show', $sub) }}"
                               class="w-8 h-8 bg-primary-100 text-primary-600 rounded flex items-center justify-center">
                                <iconify-icon icon="lucide:eye"></iconify-icon>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-secondary-light py-8">
                            <iconify-icon icon="lucide:inbox" class="text-3xl block mx-auto mb-2"></iconify-icon>
                            Belum ada data langganan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $subscriptions->links() }}
        </div>

    </div>
</div>
@endsection
