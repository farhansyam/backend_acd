@extends('layouts.app')
@section('title', 'Detail Komplain #' . $complaint->id)
@section('page-title', 'Detail Komplain')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li><a href="{{ route('complaints.index') }}" class="dark:text-white hover:text-primary-600">Komplain</a></li>
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">Komplain #{{ $complaint->id }}</li>
@endsection

@section('content')

@php
    $statusMap = [
        'open'             => ['label' => 'Menunggu Tinjauan', 'class' => 'bg-danger-100 text-danger-600'],
        'in_review'        => ['label' => 'Sedang Ditinjau', 'class' => 'bg-warning-100 text-warning-600'],
        'rework_assigned'  => ['label' => 'Teknisi Ditugaskan', 'class' => 'bg-info-100 text-info-600'],
        'rework_completed' => ['label' => 'Rework Selesai', 'class' => 'bg-purple-100 text-purple-600'],
        'closed'           => ['label' => 'Selesai', 'class' => 'bg-success-100 text-success-600'],
    ];
    $s = $statusMap[$complaint->status] ?? ['label' => $complaint->status, 'class' => 'bg-neutral-100 text-neutral-600'];
@endphp

<div class="flex flex-wrap gap-3 mb-6">
    <a href="{{ route('complaints.index') }}" class="btn btn-neutral-200 flex items-center gap-2">
        <iconify-icon icon="lucide:arrow-left"></iconify-icon>
        Kembali
    </a>
</div>

@if(session('success'))
    <div class="bg-success-100 text-success-600 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
        <iconify-icon icon="lucide:check-circle"></iconify-icon>
        {{ session('success') }}
    </div>
@endif

<div class="grid grid-cols-12 gap-6">

    {{-- ===== KOLOM KIRI — Info Komplain ===== --}}
    <div class="col-span-12 lg:col-span-8 flex flex-col gap-6">

        {{-- Header --}}
        <div class="card border-0">
            <div class="card-body p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h6 class="text-xl font-bold mb-1">{{ $complaint->title }}</h6>
                        <p class="text-sm text-secondary-light">
                            Diajukan {{ $complaint->created_at->format('d M Y, H:i') }}
                        </p>
                    </div>
                    <span class="px-3 py-1.5 rounded-full text-sm font-semibold {{ $s['class'] }}">
                        {{ $s['label'] }}
                    </span>
                </div>

                {{-- Masa garansi --}}
                @if($complaint->warranty_expires_at)
                <div class="flex items-center gap-2 p-3 rounded-lg {{ now()->lt($complaint->warranty_expires_at) ? 'bg-success-50' : 'bg-danger-50' }}">
                    <iconify-icon icon="lucide:shield" class="{{ now()->lt($complaint->warranty_expires_at) ? 'text-success-600' : 'text-danger-600' }}"></iconify-icon>
                    <span class="text-sm font-medium {{ now()->lt($complaint->warranty_expires_at) ? 'text-success-700' : 'text-danger-700' }}">
                        Masa Garansi:
                        {{ now()->lt($complaint->warranty_expires_at)
                            ? 'Aktif hingga ' . $complaint->warranty_expires_at->format('d M Y H:i')
                            : 'Kadaluarsa ' . $complaint->warranty_expires_at->format('d M Y H:i') }}
                    </span>
                </div>
                @endif
            </div>
        </div>

        {{-- Customer & Order --}}
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-3 px-5">
                <h6 class="font-semibold text-sm mb-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:user" class="text-primary-600"></iconify-icon>
                    Customer & Order
                </h6>
            </div>
            <div class="card-body p-5 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center font-bold text-primary-600">
                        {{ strtoupper(substr($complaint->user->name ?? 'C', 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-semibold">{{ $complaint->user->name ?? '-' }}</p>
                        <p class="text-sm text-secondary-light">Order #{{ $complaint->order_id }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <iconify-icon icon="lucide:calendar" class="text-primary-600"></iconify-icon>
                    <span>Jadwal: {{ $complaint->order->scheduled_date?->format('d M Y') }} · {{ $complaint->order->scheduled_time }}</span>
                </div>
                <div class="flex items-start gap-2 text-sm">
                    <iconify-icon icon="lucide:map-pin" class="text-primary-600 mt-0.5"></iconify-icon>
                    <span>{{ $complaint->order->address?->formatted_address ?? '-' }}</span>
                </div>
            </div>
        </div>

        {{-- Deskripsi komplain --}}
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-3 px-5">
                <h6 class="font-semibold text-sm mb-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:message-circle" class="text-primary-600"></iconify-icon>
                    Deskripsi Komplain
                </h6>
            </div>
            <div class="card-body p-5">
                <p class="text-sm leading-relaxed">{{ $complaint->description }}</p>
                @if($complaint->photo)
                    <div class="mt-4">
                        <p class="text-xs text-secondary-light mb-2">Foto Bukti</p>
                        <a href="{{ $complaint->photo }}" target="_blank">
                            <img src="{{ asset('storage/'.$complaint->photo) }}" alt="Foto komplain"
                                 class="w-full max-w-xs rounded-lg border border-neutral-200 hover:opacity-90 transition">
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Komentar BP (kalau ada) --}}
        @if($complaint->bp_comment)
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-3 px-5">
                <h6 class="font-semibold text-sm mb-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:message-square" class="text-warning-600"></iconify-icon>
                    Komentar BP
                </h6>
            </div>
            <div class="card-body p-5">
                <p class="text-sm leading-relaxed">{{ $complaint->bp_comment }}</p>
            </div>
        </div>
        @endif

    </div>

    {{-- ===== KOLOM KANAN — Teknisi & Aksi ===== --}}
    <div class="col-span-12 lg:col-span-4 flex flex-col gap-6">

        {{-- Info Teknisi --}}
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-3 px-5">
                <h6 class="font-semibold text-sm mb-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:user-check" class="text-primary-600"></iconify-icon>
                    Teknisi
                </h6>
            </div>
            <div class="card-body p-5 space-y-3">
                {{-- Teknisi pertama --}}
                <div>
                    <p class="text-xs text-secondary-light mb-2">Teknisi Pertama</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center font-bold text-primary-600">
                            {{ strtoupper(substr($complaint->technician->user->name ?? 'T', 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-sm">{{ $complaint->technician->user->name ?? '-' }}</p>
                            <p class="text-xs text-secondary-light capitalize">{{ $complaint->technician->grade ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Teknisi rework --}}
                @if($complaint->reworkTechnician)
                <hr class="border-neutral-100 dark:border-neutral-700">
                <div>
                    <p class="text-xs text-secondary-light mb-2">Teknisi Rework</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-info-100 rounded-full flex items-center justify-center font-bold text-info-600">
                            {{ strtoupper(substr($complaint->reworkTechnician->user->name ?? 'R', 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-sm">{{ $complaint->reworkTechnician->user->name ?? '-' }}</p>
                            <p class="text-xs text-secondary-light capitalize">{{ $complaint->reworkTechnician->grade ?? '-' }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Panel Aksi --}}
        @if($complaint->status !== 'closed')
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-3 px-5">
                <h6 class="font-semibold text-sm mb-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:settings" class="text-primary-600"></iconify-icon>
                    Aksi
                </h6>
            </div>
            <div class="card-body p-5">
                <form action="{{ route('complaints.update', $complaint) }}" method="POST" class="space-y-4">
                    @csrf @method('PATCH')

                    {{-- Pilih aksi --}}
                    <div>
                        <label class="form-label fw-semibold text-primary-light text-sm mb-2">Aksi</label>
                        <select name="action" id="actionSelect" class="form-control radius-8" required onchange="toggleFields(this.value)">
                            <option value="">-- Pilih Aksi --</option>
                            @if($complaint->status === 'open')
                                <option value="review">Tandai Sedang Ditinjau</option>
                            @endif
                            @if(in_array($complaint->status, ['open', 'in_review']))
                                <option value="assign_rework">Assign Teknisi Rework</option>
                            @endif
                            @if(in_array($complaint->status, ['open', 'in_review', 'rework_assigned', 'rework_completed']))
                                <option value="close">Tutup Komplain</option>
                            @endif
                        </select>
                    </div>

                    {{-- Pilih teknisi rework --}}
                    <div id="techField" class="hidden">
                        <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                            Teknisi Rework <span class="text-danger-600">*</span>
                        </label>
                        <select name="rework_technician_id" class="form-control radius-8">
                            <option value="">-- Pilih Teknisi --</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}"
                                    {{ $complaint->rework_technician_id == $tech->id ? 'selected' : '' }}>
                                    {{ $tech->user->name }} · {{ ucfirst($tech->grade) }}
                                    @if($tech->id == $complaint->technician_id)
                                        (Teknisi Pertama)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-secondary-light mt-1">Hanya teknisi grade Medium & Pro</p>
                    </div>

                    {{-- Komentar BP --}}
                    <div>
                        <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                            Komentar / Catatan
                        </label>
                        <textarea name="bp_comment" rows="3" class="form-control radius-8"
                            placeholder="Tulis catatan atau komentar...">{{ old('bp_comment', $complaint->bp_comment) }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary-600 w-full flex items-center justify-center gap-2">
                        <iconify-icon icon="lucide:save"></iconify-icon>
                        Simpan
                    </button>
                </form>
            </div>
        </div>
        @else
        <div class="card border-0">
            <div class="card-body p-5">
                <div class="bg-success-50 text-success-700 px-4 py-3 rounded-lg flex items-center gap-2">
                    <iconify-icon icon="lucide:check-circle"></iconify-icon>
                    Komplain sudah ditutup pada {{ $complaint->resolved_at?->format('d M Y H:i') ?? '-' }}
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

@push('scripts')
<script>
function toggleFields(action) {
    const techField = document.getElementById('techField');
    techField.classList.toggle('hidden', action !== 'assign_rework');
}
</script>
@endpush
@endsection