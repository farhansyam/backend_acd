@extends('layouts.app')
@section('title', 'Detail Langganan #' . $subscription->id)
@section('page-title', 'Detail Langganan')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li><a href="{{ route('subscriptions.index') }}" class="dark:text-white hover:text-primary-600">Cuci Langganan</a></li>
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">Langganan #{{ $subscription->id }}</li>
@endsection

@section('content')

@php
    $statusMap = [
        'pending'   => ['label' => 'Pending',    'class' => 'bg-warning-100 text-warning-600',  'icon' => 'lucide:clock'],
        'active'    => ['label' => 'Aktif',      'class' => 'bg-info-100 text-info-600',        'icon' => 'lucide:check-circle'],
        'completed' => ['label' => 'Selesai',    'class' => 'bg-success-100 text-success-600',  'icon' => 'lucide:badge-check'],
        'cancelled' => ['label' => 'Dibatalkan', 'class' => 'bg-neutral-100 text-neutral-500',  'icon' => 'lucide:x-circle'],
    ];
    $s = $statusMap[$subscription->status] ?? ['label' => $subscription->status, 'class' => 'bg-neutral-100 text-neutral-600', 'icon' => 'lucide:circle'];

    $packageClass = match($subscription->package->type) {
        'hemat'    => 'bg-info-100 text-info-600',
        'rutin'    => 'bg-warning-100 text-warning-600',
        'intensif' => 'bg-danger-100 text-danger-600',
        default    => 'bg-neutral-100 text-neutral-600',
    };

    $completedCount = $subscription->sessions->where('status', 'completed')->count();
    $totalCount     = $subscription->package->total_sessions;
@endphp

<div class="flex flex-wrap gap-3 mb-6">
    <a href="{{ route('subscriptions.index') }}" class="btn btn-neutral-200 flex items-center gap-2">
        <iconify-icon icon="lucide:arrow-left"></iconify-icon> Kembali
    </a>
</div>

@if(session('success'))
    <div class="bg-success-100 text-success-600 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
        <iconify-icon icon="lucide:check-circle"></iconify-icon>
        {{ session('success') }}
    </div>
@endif

<div class="grid grid-cols-12 gap-6">

    {{-- ===== KOLOM KIRI ===== --}}
    <div class="col-span-12 lg:col-span-4 flex flex-col gap-6">

        {{-- Header Status --}}
        <div class="card border-0">
            <div class="card-body p-6">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h6 class="text-xl font-bold mb-1">Langganan #{{ $subscription->id }}</h6>
                        <p class="text-sm text-secondary-light">Dibuat {{ $subscription->created_at->format('d M Y, H:i') }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1 {{ $s['class'] }}">
                        <iconify-icon icon="{{ $s['icon'] }}"></iconify-icon>
                        {{ $s['label'] }}
                    </span>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $packageClass }}">
                    {{ $subscription->package->name }}
                </span>
                <span class="ml-1 px-3 py-1 rounded-full text-xs font-semibold {{ $subscription->payment_status === 'paid' ? 'bg-success-100 text-success-600' : 'bg-neutral-100 text-neutral-500' }}">
                    {{ $subscription->payment_status === 'paid' ? '✓ Lunas' : 'Belum Bayar' }}
                </span>
            </div>
        </div>

        {{-- Info Customer --}}
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-3 px-5">
                <h6 class="font-semibold text-sm mb-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:user" class="text-primary-600"></iconify-icon> Customer
                </h6>
            </div>
            <div class="card-body p-5">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center text-xl font-bold">
                        {{ strtoupper(substr($subscription->user->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-semibold">{{ $subscription->user->name }}</p>
                        <p class="text-sm text-secondary-light">{{ $subscription->user->email }}</p>
                        <p class="text-sm text-secondary-light">{{ $subscription->userPhone->phone_number ?? '-' }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                        <iconify-icon icon="lucide:map-pin" class="text-primary-600"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-light">Alamat</p>
                        <p class="font-medium text-sm">{{ $subscription->address->label ?? '-' }}</p>
                        <p class="text-sm text-secondary-light">{{ $subscription->address->full_address ?? '-' }}</p>
                        <p class="text-xs text-secondary-light">{{ $subscription->address->city_name ?? '' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Info Paket --}}
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-3 px-5">
                <h6 class="font-semibold text-sm mb-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:box" class="text-warning-600"></iconify-icon> Detail Paket
                </h6>
            </div>
            <div class="card-body p-5 space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-secondary-light">Paket</span>
                    <span class="text-sm font-semibold">{{ $subscription->package->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-secondary-light">Interval</span>
                    <span class="text-sm">Setiap {{ $subscription->package->interval_months }} bulan</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-secondary-light">Total Sesi</span>
                    <span class="text-sm">{{ $subscription->package->total_sessions }}x cuci</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-secondary-light">Progress</span>
                    <span class="text-sm font-semibold">{{ $completedCount }}/{{ $totalCount }} selesai</span>
                </div>
                @if($subscription->starts_at)
                <div class="flex justify-between">
                    <span class="text-sm text-secondary-light">Mulai</span>
                    <span class="text-sm">{{ $subscription->starts_at->format('d M Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-secondary-light">Berakhir</span>
                    <span class="text-sm">{{ $subscription->expires_at->format('d M Y') }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Pembayaran --}}
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-3 px-5">
                <h6 class="font-semibold text-sm mb-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:credit-card" class="text-success-600"></iconify-icon> Pembayaran
                </h6>
            </div>
            <div class="card-body p-5 space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-secondary-light">Subtotal</span>
                    <span class="text-sm">Rp {{ number_format($subscription->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($subscription->discount_amount > 0)
                <div class="flex justify-between">
                    <span class="text-sm text-secondary-light">Diskon</span>
                    <span class="text-sm text-success-600">- Rp {{ number_format($subscription->discount_amount, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex justify-between border-t border-neutral-100 pt-3">
                    <span class="font-semibold text-sm">Total Bayar</span>
                    <span class="font-bold text-primary-600">Rp {{ number_format($subscription->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-secondary-light">Metode</span>
                    <span class="text-sm font-medium">{{ $subscription->payment_method ?? '-' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-secondary-light">Status</span>
                    @if($subscription->payment_status === 'paid')
                        <span class="bg-success-100 text-success-600 px-3 py-1 rounded-full text-xs font-semibold">✓ Lunas</span>
                    @else
                        <span class="bg-neutral-100 text-neutral-500 px-3 py-1 rounded-full text-xs font-semibold">Belum Bayar</span>
                    @endif
                </div>
                @if($subscription->paid_at)
                <div class="flex justify-between">
                    <span class="text-sm text-secondary-light">Dibayar</span>
                    <span class="text-sm">{{ $subscription->paid_at->format('d M Y H:i') }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Service Dipilih --}}
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-3 px-5">
                <h6 class="font-semibold text-sm mb-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:list-checks" class="text-info-600"></iconify-icon> Service Dipilih
                </h6>
            </div>
            <div class="card-body p-0">
                @foreach($subscription->items as $item)
                <div class="flex items-center justify-between px-5 py-3 border-b border-neutral-100 dark:border-neutral-700 last:border-0">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <iconify-icon icon="lucide:wind" class="text-primary-600 text-sm"></iconify-icon>
                        </div>
                        <div>
                            <p class="text-sm font-medium">{{ $item->bpService->serviceType->name }}</p>
                            <p class="text-xs text-secondary-light">{{ $item->quantity }} unit × Rp {{ number_format($item->unit_price, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <p class="text-sm font-semibold">Rp {{ number_format($item->subtotal_per_session, 0, ',', '.') }}/sesi</p>
                </div>
                @endforeach
            </div>
        </div>

    </div>

    {{-- ===== KOLOM KANAN: Jadwal Sesi ===== --}}
    <div class="col-span-12 lg:col-span-8">
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-3 px-5 flex items-center justify-between">
                <h6 class="font-semibold text-sm mb-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:calendar-days" class="text-primary-600"></iconify-icon> Jadwal Sesi
                </h6>
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-primary-100 text-primary-600">
                    {{ $completedCount }}/{{ $totalCount }} selesai
                </span>
            </div>
            <div class="card-body p-0">
                @if($subscription->sessions->isEmpty())
                    <div class="text-center py-12 text-secondary-light">
                        <iconify-icon icon="lucide:calendar-x" class="text-4xl block mx-auto mb-3"></iconify-icon>
                        @if($subscription->payment_status !== 'paid')
                            <p class="font-medium">Belum ada jadwal</p>
                            <p class="text-sm mt-1">Customer belum melakukan pembayaran.</p>
                        @else
                            <p class="font-medium">Belum ada jadwal</p>
                            <p class="text-sm mt-1">Customer belum mengatur jadwal sesi.</p>
                        @endif
                    </div>
                @else
                    <div class="divide-y divide-neutral-100 dark:divide-neutral-700">
                        @foreach($subscription->sessions as $session)
                        @php
                            $sessionStatusMap = [
                                'scheduled'            => ['label' => 'Terjadwal',           'class' => 'bg-neutral-100 text-neutral-600',   'icon' => 'lucide:calendar'],
                                'confirmed'            => ['label' => 'Dikonfirmasi',         'class' => 'bg-info-100 text-info-600',         'icon' => 'lucide:check-circle'],
                                'in_progress'          => ['label' => 'Sedang Dikerjakan',   'class' => 'bg-warning-100 text-warning-600',   'icon' => 'lucide:wrench'],
                                'waiting_confirmation' => ['label' => 'Menunggu Konfirmasi', 'class' => 'bg-purple-100 text-purple-600',     'icon' => 'lucide:clock'],
                                'completed'            => ['label' => 'Selesai',             'class' => 'bg-success-100 text-success-600',   'icon' => 'lucide:badge-check'],
                                'cancelled'            => ['label' => 'Dibatalkan',          'class' => 'bg-danger-100 text-danger-600',     'icon' => 'lucide:x-circle'],
                            ];
                            $ss = $sessionStatusMap[$session->status] ?? ['label' => $session->status, 'class' => 'bg-neutral-100 text-neutral-600', 'icon' => 'lucide:circle'];
                            $canAssign = in_array($session->status, ['scheduled', 'confirmed']) && $subscription->payment_status === 'paid';
                        @endphp
                        <div class="px-5 py-4">
                            <div class="flex items-start gap-4">

                                {{-- Nomor Sesi --}}
                                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 font-bold text-sm
                                    {{ $session->status === 'completed' ? 'bg-success-100 text-success-600' : 'bg-primary-100 text-primary-600' }}">
                                    {{ $session->session_number }}
                                </div>

                                {{-- Info --}}
                                <div class="flex-1">
                                    <div class="flex items-center justify-between flex-wrap gap-2">
                                        <div>
                                            <p class="font-semibold text-sm">Sesi ke-{{ $session->session_number }}</p>
                                            <p class="text-sm text-secondary-light flex items-center gap-1 mt-0.5">
                                                <iconify-icon icon="lucide:calendar"></iconify-icon>
                                                {{ \Carbon\Carbon::parse($session->scheduled_date)->translatedFormat('d F Y') }}
                                                · {{ $session->scheduled_time }}
                                            </p>
                                        </div>
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $ss['class'] }} flex items-center gap-1">
                                            <iconify-icon icon="{{ $ss['icon'] }}"></iconify-icon>
                                            {{ $ss['label'] }}
                                        </span>
                                    </div>

                                    {{-- Teknisi --}}
                                    <div class="mt-3 flex items-center justify-between flex-wrap gap-2">
                                        @if($session->technician)
                                            <div class="flex items-center gap-2">
                                                <div class="w-7 h-7 bg-success-100 rounded-full flex items-center justify-center">
                                                    <iconify-icon icon="lucide:user" class="text-success-600 text-xs"></iconify-icon>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium">{{ $session->technician->user->name }}</p>
                                                    <p class="text-xs text-secondary-light capitalize">{{ $session->technician->grade }}</p>
                                                </div>
                                            </div>
                                        @else
                                            <div class="flex items-center gap-2 text-warning-600 text-sm">
                                                <iconify-icon icon="lucide:alert-triangle"></iconify-icon>
                                                Belum di-assign
                                            </div>
                                        @endif

                                        {{-- Tombol Aksi --}}
                                        <div class="flex items-center gap-2">
                                            @if($session->report)
                                                <button class="btn btn-sm btn-info-100 text-info-600 flex items-center gap-1 text-xs px-3 py-1.5"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#reportModal{{ $session->id }}">
                                                    <iconify-icon icon="lucide:file-text"></iconify-icon> Laporan
                                                </button>
                                            @endif
                                            @if($canAssign)
                                                <button class="btn btn-sm btn-primary-600 flex items-center gap-1 text-xs px-3 py-1.5"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#assignModal{{ $session->id }}">
                                                    <iconify-icon icon="lucide:user-check"></iconify-icon>
                                                    {{ $session->technician ? 'Ganti Teknisi' : 'Assign' }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>{{-- /grid --}}

{{-- ─── Modal Assign Teknisi ──────────────────────────────────────────────── --}}
@foreach($subscription->sessions as $session)
    @if(in_array($session->status, ['scheduled', 'confirmed']) && $subscription->payment_status === 'paid')
    <div class="modal fade" id="assignModal{{ $session->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-b border-neutral-200">
                    <h5 class="modal-title font-semibold flex items-center gap-2">
                        <iconify-icon icon="lucide:user-check" class="text-primary-600"></iconify-icon>
                        Assign Teknisi — Sesi ke-{{ $session->session_number }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('subscriptions.sessions.assign', [$subscription, $session]) }}">
                    @csrf
                    <div class="modal-body p-5 space-y-4">
                        <div class="bg-neutral-50 rounded-lg p-3 flex items-center gap-2 text-sm">
                            <iconify-icon icon="lucide:calendar" class="text-primary-600"></iconify-icon>
                            <span class="font-medium">
                                {{ \Carbon\Carbon::parse($session->scheduled_date)->translatedFormat('d F Y') }}
                            </span>
                            <span class="text-secondary-light">pukul {{ $session->scheduled_time }}</span>
                        </div>
                        <div>
                            <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                Pilih Teknisi <span class="text-danger-600">*</span>
                            </label>
                            <select name="technician_id" class="form-control radius-8" required>
                                <option value="">-- Pilih teknisi --</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}"
                                        {{ $session->technician_id == $tech->id ? 'selected' : '' }}>
                                        {{ $tech->user->name }} · {{ ucfirst($tech->grade) }}
                                    </option>
                                @endforeach
                            </select>
                            @if($technicians->isEmpty())
                                <p class="text-xs text-danger-600 mt-1">Belum ada teknisi terdaftar untuk mitra ini.</p>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer border-t border-neutral-200 gap-2">
                        <button type="button" class="btn btn-neutral-200 btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary-600 btn-sm flex items-center gap-2"
                            {{ $technicians->isEmpty() ? 'disabled' : '' }}>
                            <iconify-icon icon="lucide:user-check"></iconify-icon> Assign
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach

{{-- ─── Modal Laporan Sesi ─────────────────────────────────────────────────── --}}
@foreach($subscription->sessions as $session)
    @if($session->report)
    <div class="modal fade" id="reportModal{{ $session->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-b border-neutral-200">
                    <h5 class="modal-title font-semibold flex items-center gap-2">
                        <iconify-icon icon="lucide:file-text" class="text-info-600"></iconify-icon>
                        Laporan Sesi ke-{{ $session->session_number }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-5">

                    {{-- Foto --}}
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <p class="text-xs text-secondary-light mb-1 font-medium">📷 Sebelum</p>
                            <img src="{{ asset('storage/' . $session->report->photo_before) }}"
                                class="w-full rounded-lg object-cover border border-neutral-200"
                                style="height:160px;" alt="Sebelum">
                        </div>
                        <div>
                            <p class="text-xs text-secondary-light mb-1 font-medium">📷 Sesudah</p>
                            <img src="{{ asset('storage/' . $session->report->photo_after) }}"
                                class="w-full rounded-lg object-cover border border-neutral-200"
                                style="height:160px;" alt="Sesudah">
                        </div>
                    </div>

                    {{-- Checklist --}}
                    <p class="text-xs font-semibold text-secondary-light uppercase tracking-wide mb-3">Checklist</p>
                    <div class="grid grid-cols-2 gap-2">
                        @php
                            $checklistItems = [
                                'filter_cleaned'     => 'Filter dibersihkan',
                                'freon_checked'      => 'Freon dicek',
                                'drain_cleaned'      => 'Saluran air dibersihkan',
                                'electrical_checked' => 'Kelistrikan dicek',
                                'unit_installed'     => 'Unit terpasang',
                                'piping_neat'        => 'Pipa rapi',
                                'cooling_test'       => 'Tes pendinginan',
                                'remote_working'     => 'Remote berfungsi',
                            ];
                        @endphp
                        @foreach($checklistItems as $key => $label)
                        <div class="flex items-center gap-2 text-sm">
                            @if($session->report->$key)
                                <iconify-icon icon="lucide:check-circle" class="text-success-600 flex-shrink-0"></iconify-icon>
                            @else
                                <iconify-icon icon="lucide:x-circle" class="text-danger-600 flex-shrink-0"></iconify-icon>
                            @endif
                            <span>{{ $label }}</span>
                        </div>
                        @endforeach
                    </div>

                    @if($session->report->notes)
                    <div class="mt-4">
                        <p class="text-xs font-semibold text-secondary-light uppercase tracking-wide mb-2">Catatan Teknisi</p>
                        <p class="text-sm bg-neutral-50 rounded-lg p-3">{{ $session->report->notes }}</p>
                    </div>
                    @endif

                </div>
                <div class="modal-footer border-t border-neutral-200">
                    <button type="button" class="btn btn-neutral-200 btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach

@endsection
