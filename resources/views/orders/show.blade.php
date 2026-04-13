@extends('layouts.app')
@section('title', 'Detail Order #' . $order->id)
@section('page-title', 'Detail Order')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li><a href="{{ route('orders.index') }}" class="dark:text-white hover:text-primary-600">Order</a></li>
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">Order #{{ $order->id }}</li>
@endsection

@section('content')

@php
    $statusMap = [
        'confirmed'            => ['label' => 'Dikonfirmasi', 'class' => 'bg-info-100 text-info-600', 'icon' => 'lucide:check-circle'],
        'in_progress'          => ['label' => 'Sedang Dikerjakan', 'class' => 'bg-warning-100 text-warning-600', 'icon' => 'lucide:wrench'],
        'waiting_confirmation' => ['label' => 'Menunggu Konfirmasi', 'class' => 'bg-purple-100 text-purple-600', 'icon' => 'lucide:clock'],
        'completed'            => ['label' => 'Selesai', 'class' => 'bg-success-100 text-success-600', 'icon' => 'lucide:badge-check'],
    ];
    $s = $statusMap[$order->status] ?? ['label' => $order->status, 'class' => 'bg-neutral-100 text-neutral-600', 'icon' => 'lucide:circle'];
@endphp

<div class="flex flex-wrap gap-3 mb-6">
    <a href="{{ route('orders.index') }}" class="btn btn-neutral-200 flex items-center gap-2">
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

    {{-- ===== KOLOM KIRI — Info Order ===== --}}
    <div class="col-span-12 lg:col-span-8 flex flex-col gap-6">

        {{-- Status & Header --}}
        <div class="card border-0">
            <div class="card-body p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h6 class="text-xl font-bold mb-1">Order #{{ $order->id }}</h6>
                        <p class="text-sm text-secondary-light">Dibuat {{ $order->created_at->format('d M Y, H:i') }}</p>
                    </div>
                    <span class="px-4 py-2 rounded-full text-sm font-semibold flex items-center gap-2 {{ $s['class'] }}">
                        <iconify-icon icon="{{ $s['icon'] }}"></iconify-icon>
                        {{ $s['label'] }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Customer --}}
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-3 px-5">
                <h6 class="font-semibold text-sm mb-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:user" class="text-primary-600"></iconify-icon>
                    Customer
                </h6>
            </div>
            <div class="card-body p-5">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center text-xl font-bold">
                        {{ strtoupper(substr($order->user->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-semibold">{{ $order->user->name }}</p>
                        <p class="text-sm text-secondary-light">{{ $order->user->email }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Jadwal & Lokasi --}}
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-3 px-5">
                <h6 class="font-semibold text-sm mb-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:calendar-clock" class="text-primary-600"></iconify-icon>
                    Jadwal & Lokasi
                </h6>
            </div>
            <div class="card-body p-5 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-info-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <iconify-icon icon="lucide:calendar" class="text-info-600"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-light">Jadwal</p>
                        <p class="font-semibold text-sm">
                            {{ \Carbon\Carbon::parse($order->scheduled_date)->format('d M Y') }} · {{ $order->scheduled_time }}
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                        <iconify-icon icon="lucide:map-pin" class="text-primary-600"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-light">Lokasi</p>
                        <p class="font-semibold text-sm">{{ $order->address?->label }}</p>
                        <p class="text-sm text-secondary-light">{{ $order->address?->formatted_address }}</p>
                        @if($order->address?->notes)
                            <p class="text-sm text-warning-600 mt-1 flex items-center gap-1">
                                <iconify-icon icon="lucide:alert-circle"></iconify-icon>
                                {{ $order->address->notes }}
                            </p>
                        @endif
                    </div>
                </div>

                     {{-- Tambah setelah info alamat, masih di dalam card Jadwal & Lokasi --}}
                        @if($order->address?->latitude && $order->address?->longitude)
                        <div class="mt-4">
                            <p class="text-xs text-secondary-light mb-2">Peta Lokasi</p>
                            <div id="map" class="w-full rounded-lg overflow-hidden border border-neutral-200"
                                style="height: 220px; z-index: 0;"></div>
                        </div>
                        @endif
                
           

                @if($order->notes)
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 bg-warning-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                        <iconify-icon icon="lucide:sticky-note" class="text-warning-600"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-light">Catatan Customer</p>
                        <p class="text-sm">{{ $order->notes }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Layanan --}}
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-3 px-5">
                <h6 class="font-semibold text-sm mb-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:wind" class="text-primary-600"></iconify-icon>
                    Layanan Dipesan
                </h6>
            </div>
            <div class="card-body p-5">
                <div class="space-y-3">
                    @foreach($order->items as $item)
                    <div class="flex items-center justify-between py-2 border-b border-neutral-100 dark:border-neutral-700 last:border-0">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <iconify-icon icon="lucide:wind" class="text-primary-600 text-sm"></iconify-icon>
                            </div>
                            <div>
                                <p class="font-medium text-sm">{{ $item->bpService?->serviceType?->name }}</p>
                                <p class="text-xs text-secondary-light">
                                    {{ $item->quantity }} unit × Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                        <p class="font-semibold text-sm">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                    </div>
                    @endforeach

                    <div class="pt-2">
                        @if($order->discount_amount > 0)
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-secondary-light">Diskon</span>
                            <span class="text-success-600">- Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between font-bold text-base mt-2 pt-2 border-t border-neutral-200 dark:border-neutral-600">
                            <span>Total</span>
                            <span class="text-primary-600">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ===== KOLOM KANAN — Teknisi & Pembayaran ===== --}}
    <div class="col-span-12 lg:col-span-4 flex flex-col gap-6">

        {{-- Assign Teknisi --}}
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-3 px-5">
                <h6 class="font-semibold text-sm mb-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:user-check" class="text-primary-600"></iconify-icon>
                    Teknisi
                </h6>
            </div>
            <div class="card-body p-5">
                @if($order->technician)
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-full bg-success-100 text-success-600 flex items-center justify-center text-xl font-bold">
                            {{ strtoupper(substr($order->technician->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-bold">{{ $order->technician->user->name }}</p>
                            <p class="text-sm text-secondary-light capitalize">{{ $order->technician->grade }}</p>
                            <p class="text-sm text-secondary-light">{{ $order->technician->city }}</p>
                        </div>
                    </div>
                    <div class="bg-success-50 dark:bg-success-600/10 text-success-600 px-3 py-2 rounded-lg text-sm flex items-center gap-2">
                        <iconify-icon icon="lucide:check-circle"></iconify-icon>
                        Sudah di-assign
                    </div>
                @else
                    <div class="bg-warning-50 dark:bg-warning-600/10 text-warning-600 px-3 py-2 rounded-lg text-sm flex items-center gap-2 mb-4">
                        <iconify-icon icon="lucide:alert-triangle"></iconify-icon>
                        Belum ada teknisi
                    </div>

                    @if($order->status === 'confirmed')
                        @if($technicians->isEmpty())
                            <div class="bg-danger-50 text-danger-600 px-3 py-2 rounded-lg text-sm flex items-center gap-2">
                                <iconify-icon icon="lucide:users-x"></iconify-icon>
                                Tidak ada teknisi tersedia
                            </div>
                        @else
                        <form action="{{ route('orders.assign', $order) }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                    Pilih Teknisi <span class="text-danger-600">*</span>
                                </label>
                                <select name="technician_id" class="form-control radius-8" required>
                                    <option value="">-- Pilih Teknisi --</option>
                                    @foreach($technicians as $tech)
                                        <option value="{{ $tech->id }}">
                                            {{ $tech->user->name }} · {{ ucfirst($tech->grade) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('technician_id')
                                    <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                    Catatan untuk Teknisi
                                </label>
                                <textarea name="notes" class="form-control radius-8" rows="3"
                                    placeholder="Catatan khusus untuk teknisi...">{{ old('notes') }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary-600 w-full flex items-center justify-center gap-2">
                                <iconify-icon icon="lucide:user-plus"></iconify-icon>
                                Assign Teknisi
                            </button>
                        </form>
                        @endif
                    @endif
                @endif
            </div>
        </div>

        {{-- Pembayaran --}}
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-3 px-5">
                <h6 class="font-semibold text-sm mb-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:credit-card" class="text-primary-600"></iconify-icon>
                    Pembayaran
                </h6>
            </div>
            <div class="card-body p-5 space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-secondary-light">Metode</span>
                    <span class="text-sm font-medium">{{ $order->payment_method ?? '-' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-secondary-light">Status</span>
                    @if($order->payment_status === 'paid')
                        <span class="bg-success-100 text-success-600 px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1">
                            <iconify-icon icon="lucide:check"></iconify-icon> Lunas
                        </span>
                    @else
                        <span class="bg-danger-100 text-danger-600 px-3 py-1 rounded-full text-xs font-semibold">
                            Belum Bayar
                        </span>
                    @endif
                </div>
                @if($order->paid_at)
                <div class="flex justify-between items-center">
                    <span class="text-sm text-secondary-light">Dibayar</span>
                    <span class="text-sm font-medium">
                        {{ \Carbon\Carbon::parse($order->paid_at)->format('d M Y H:i') }}
                    </span>
                </div>
                @endif
                @if($order->tripay_reference)
                <div class="flex justify-between items-center">
                    <span class="text-sm text-secondary-light">Ref. Tripay</span>
                    <span class="text-xs font-mono text-secondary-light">{{ $order->tripay_reference }}</span>
                </div>
                @endif
            </div>
        </div>

    </div>
</div>
@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

@if($order->address?->latitude && $order->address?->longitude)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const lat = {{ $order->address->latitude }};
        const lng = {{ $order->address->longitude }};

        const map = L.map('map').setView([lat, lng], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        L.marker([lat, lng])
            .addTo(map)
            .bindPopup(`
                <strong>{{ $order->address?->label }}</strong><br>
                {{ $order->address?->formatted_address }}<br>
                {{ $order->address?->village_name }}, {{ $order->address?->district_name }}
            `)
            .openPopup();
    });
</script>
@endif
@endpush
@endsection