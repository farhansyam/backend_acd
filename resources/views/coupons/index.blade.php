@extends('layouts.app')
@section('title', 'Kupon')
@section('page-title', 'Manajemen Kupon')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">Kupon</li>
@endsection

@section('content')
<div class="card border-0 mt-6">
    <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6 flex items-center justify-between">
        <h6 class="text-lg font-semibold mb-0">Daftar Kupon</h6>
        <a href="{{ route('coupons.create') }}" class="btn btn-primary-600 flex items-center gap-2">
            <iconify-icon icon="lucide:plus"></iconify-icon> Buat Kupon
        </a>
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
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Diskon</th>
                        <th>Min Order</th>
                        <th>Berlaku</th>
                        <th>Dipakai</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $coupon)
                    @php
                        $isExpired = now()->gt($coupon->valid_until);
                        $isNotStarted = now()->lt($coupon->valid_from);
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <span class="font-mono font-bold text-primary-600 bg-primary-50 px-2 py-1 rounded text-sm">
                                {{ $coupon->code }}
                            </span>
                        </td>
                        <td>{{ $coupon->name }}</td>
                        <td>
                            <span class="font-bold">{{ $coupon->discount_percent }}%</span>
                            @if($coupon->max_discount)
                                <span class="text-xs text-secondary-light block">
                                    maks Rp {{ number_format($coupon->max_discount, 0, ',', '.') }}
                                </span>
                            @endif
                        </td>
                        <td>Rp {{ number_format($coupon->min_order, 0, ',', '.') }}</td>
                        <td>
                            <p class="text-sm">{{ $coupon->valid_from->format('d M Y') }}</p>
                            <p class="text-xs text-secondary-light">s/d {{ $coupon->valid_until->format('d M Y') }}</p>
                            @if($isExpired)
                                <span class="text-xs text-danger-600 font-medium">Kadaluarsa</span>
                            @elseif($isNotStarted)
                                <span class="text-xs text-warning-600 font-medium">Belum mulai</span>
                            @endif
                        </td>
                        <td>{{ $coupon->usages_count }}x</td>
                        <td>
                            <form action="{{ route('coupons.toggle', $coupon) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="px-3 py-1 rounded-full text-xs font-medium cursor-pointer
                                        {{ $coupon->is_active ? 'bg-success-100 text-success-600' : 'bg-neutral-100 text-neutral-500' }}">
                                    {{ $coupon->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </form>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('coupons.edit', $coupon) }}"
                                   class="w-8 h-8 bg-warning-100 text-warning-600 rounded flex items-center justify-center">
                                    <iconify-icon icon="lucide:pencil"></iconify-icon>
                                </a>
                                <form action="{{ route('coupons.destroy', $coupon) }}" method="POST"
                                      onsubmit="return confirm('Yakin hapus kupon ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="w-8 h-8 bg-danger-100 text-danger-600 rounded flex items-center justify-center">
                                        <iconify-icon icon="lucide:trash-2"></iconify-icon>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-secondary-light py-6">Belum ada kupon.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $coupons->links() }}</div>
    </div>
</div>
@endsection