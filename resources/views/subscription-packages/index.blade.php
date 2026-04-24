@extends('layouts.app')
@section('title', 'Paket Cuci Langganan')
@section('page-title', 'Paket Cuci Langganan')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">Paket Langganan</li>
@endsection

@section('content')
<div class="card border-0 mt-6">
    <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6 flex items-center justify-between">
        <h6 class="text-lg font-semibold mb-0">Paket Cuci Langganan</h6>
        <button class="btn btn-primary-600 flex items-center gap-2"
            data-bs-toggle="modal" data-bs-target="#createModal">
            <iconify-icon icon="lucide:plus"></iconify-icon> Tambah Paket
        </button>
    </div>
    <div class="card-body p-6">

        @if(session('success'))
            <div class="bg-success-100 text-success-600 px-4 py-3 rounded mb-4 flex items-center gap-2">
                <iconify-icon icon="lucide:check-circle"></iconify-icon>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-danger-100 text-danger-600 px-4 py-3 rounded mb-4 flex items-center gap-2">
                <iconify-icon icon="lucide:alert-circle"></iconify-icon>
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @forelse($packages as $package)
            @php
                $packageClass = match($package->type) {
                    'hemat'    => ['bg' => 'bg-info-100',    'text' => 'text-info-600',    'border' => 'border-info-200'],
                    'rutin'    => ['bg' => 'bg-warning-100', 'text' => 'text-warning-600', 'border' => 'border-warning-200'],
                    'intensif' => ['bg' => 'bg-danger-100',  'text' => 'text-danger-600',  'border' => 'border-danger-200'],
                    default    => ['bg' => 'bg-neutral-100', 'text' => 'text-neutral-600', 'border' => 'border-neutral-200'],
                };
                $discountPct = round((1 - $package->price_multiplier) * 100);
            @endphp
            <div class="card border {{ $packageClass['border'] }} border-0 shadow-sm">
                <div class="card-body p-5">
                    {{-- Header --}}
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $packageClass['bg'] }} {{ $packageClass['text'] }}">
                                {{ ucfirst($package->type) }}
                            </span>
                            <h6 class="font-bold text-base mt-2">{{ $package->name }}</h6>
                        </div>
                        @if($package->is_active)
                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-success-100 text-success-600">Aktif</span>
                        @else
                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-neutral-100 text-neutral-500">Nonaktif</span>
                        @endif
                    </div>

                    {{-- Stats --}}
                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-secondary-light">Interval</span>
                            <span class="font-medium">Setiap {{ $package->interval_months }} bulan</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-secondary-light">Total Sesi</span>
                            <span class="font-medium">{{ $package->total_sessions }}x / tahun</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-secondary-light">Diskon Harga</span>
                            <span class="font-bold {{ $packageClass['text'] }}">{{ $discountPct }}% lebih hemat</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-secondary-light">Multiplier</span>
                            <span class="font-medium">× {{ $package->price_multiplier }}</span>
                        </div>
                    </div>

                    @if($package->description)
                        <p class="text-xs text-secondary-light mb-4">{{ $package->description }}</p>
                    @endif

                    {{-- Contoh harga --}}
                    <div class="bg-neutral-50 dark:bg-neutral-700 rounded-lg p-3 mb-4">
                        <p class="text-xs text-secondary-light mb-1">Contoh: Cuci 1PK = Rp 100.000</p>
                        <p class="text-sm font-semibold {{ $packageClass['text'] }}">
                            Rp {{ number_format(100000 * $package->total_sessions * $package->price_multiplier, 0, ',', '.') }} / tahun
                        </p>
                        <p class="text-xs text-secondary-light line-through">
                            Normal: Rp {{ number_format(100000 * $package->total_sessions, 0, ',', '.') }}
                        </p>
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-2">
                        <button class="btn btn-warning-600 btn-sm flex-1 flex items-center justify-center gap-1"
                            data-bs-toggle="modal"
                            data-bs-target="#editModal{{ $package->id }}">
                            <iconify-icon icon="lucide:pencil"></iconify-icon> Edit
                        </button>
                        <form action="{{ route('subscription-packages.toggle', $package) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="btn btn-sm {{ $package->is_active ? 'btn-neutral-200' : 'btn-success-600' }} flex items-center gap-1">
                                <iconify-icon icon="{{ $package->is_active ? 'lucide:eye-off' : 'lucide:eye' }}"></iconify-icon>
                                {{ $package->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-3 text-center py-12 text-secondary-light">
                <iconify-icon icon="lucide:package-open" class="text-4xl block mx-auto mb-3"></iconify-icon>
                <p>Belum ada paket. Klik "Tambah Paket" untuk memulai.</p>
            </div>
            @endforelse
        </div>

    </div>
</div>

{{-- ─── Modal Tambah Paket ─────────────────────────────────────────────────── --}}
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-b border-neutral-200">
                <h5 class="modal-title font-semibold flex items-center gap-2">
                    <iconify-icon icon="lucide:plus-circle" class="text-primary-600"></iconify-icon>
                    Tambah Paket
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('subscription-packages.store') }}">
                @csrf
                <div class="modal-body p-5 space-y-4">
                    @include('subscription-packages._form', ['package' => null])
                </div>
                <div class="modal-footer border-t border-neutral-200 gap-2">
                    <button type="button" class="btn btn-neutral-200 btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-600 btn-sm flex items-center gap-2">
                        <iconify-icon icon="lucide:save"></iconify-icon> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ─── Modal Edit per Paket ───────────────────────────────────────────────── --}}
@foreach($packages as $package)
<div class="modal fade" id="editModal{{ $package->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-b border-neutral-200">
                <h5 class="modal-title font-semibold flex items-center gap-2">
                    <iconify-icon icon="lucide:pencil" class="text-warning-600"></iconify-icon>
                    Edit — {{ $package->name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('subscription-packages.update', $package) }}">
                @csrf @method('PUT')
                <div class="modal-body p-5 space-y-4">
                    @include('subscription-packages._form', ['package' => $package])
                </div>
                <div class="modal-footer border-t border-neutral-200 gap-2">
                    <button type="button" class="btn btn-neutral-200 btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning-600 btn-sm flex items-center gap-2">
                        <iconify-icon icon="lucide:save"></iconify-icon> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection
