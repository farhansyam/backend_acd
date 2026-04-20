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
    $isRelokasi    = $order->order_type === 'relokasi';
    $isDiffLoc     = $order->relocation_type === 'different_location';
    $needTransport = $isRelokasi && $isDiffLoc;

    $statusMap = [
        'pending'                   => ['label' => 'Menunggu Konfirmasi',          'class' => 'bg-warning-100 text-warning-600',   'icon' => 'lucide:clock'],
        'pending_transport_fee'     => ['label' => 'Menunggu Biaya Transportasi',  'class' => 'bg-orange-100 text-orange-600',     'icon' => 'lucide:truck'],
        'pending_transport_fee_set' => ['label' => 'Menunggu Konfirmasi Customer', 'class' => 'bg-purple-100 text-purple-600',     'icon' => 'lucide:user-check'],
        'confirmed'                 => ['label' => 'Dikonfirmasi',                 'class' => 'bg-info-100 text-info-600',         'icon' => 'lucide:check-circle'],
        'in_progress'               => ['label' => 'Sedang Dikerjakan',            'class' => 'bg-warning-100 text-warning-600',   'icon' => 'lucide:wrench'],
        'waiting_confirmation'      => ['label' => 'Menunggu Konfirmasi',          'class' => 'bg-purple-100 text-purple-600',     'icon' => 'lucide:clock'],
        'completed'                 => ['label' => 'Selesai',                      'class' => 'bg-success-100 text-success-600',   'icon' => 'lucide:badge-check'],
        'warranty'                  => ['label' => 'Masa Garansi',                 'class' => 'bg-teal-100 text-teal-600',         'icon' => 'lucide:shield'],
        'complained'                => ['label' => 'Dikomplain',                   'class' => 'bg-danger-100 text-danger-600',     'icon' => 'lucide:alert-triangle'],
        'cancelled'                 => ['label' => 'Dibatalkan',                   'class' => 'bg-neutral-100 text-neutral-500',   'icon' => 'lucide:x-circle'],
    ];
    $s = $statusMap[$order->status] ?? ['label' => $order->status, 'class' => 'bg-neutral-100 text-neutral-600', 'icon' => 'lucide:circle'];
@endphp

<div class="flex flex-wrap gap-3 mb-6">
    <a href="{{ route('orders.index') }}" class="btn btn-neutral-200 flex items-center gap-2">
        <iconify-icon icon="lucide:arrow-left"></iconify-icon> Kembali
    </a>
</div>

@if(session('success'))
    <div class="bg-success-100 text-success-600 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
        <iconify-icon icon="lucide:check-circle"></iconify-icon>
        {{ session('success') }}
    </div>
@endif

{{-- Banner relokasi beda lokasi --}}
@if($needTransport && $order->status === 'pending_transport_fee')
    <div class="bg-orange-50 border border-orange-200 rounded-xl px-5 py-4 mb-6 flex items-start gap-3">
        <iconify-icon icon="lucide:truck" class="text-orange-500 text-xl mt-0.5"></iconify-icon>
        <div>
            <p class="font-semibold text-orange-700">Order Relokasi — Beda Lokasi</p>
            <p class="text-sm text-orange-600 mt-1">
                Customer menunggu Anda menentukan biaya transportasi.
                Setelah diset, customer akan mendapat notifikasi untuk konfirmasi.
            </p>
        </div>
    </div>
@endif

<div class="grid grid-cols-12 gap-6">

    {{-- ===== KOLOM KIRI ===== --}}
    <div class="col-span-12 lg:col-span-8 flex flex-col gap-6">

        {{-- Status & Header --}}
        <div class="card border-0">
            <div class="card-body p-6">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <h6 class="text-xl font-bold mb-1">Order #{{ $order->id }}</h6>
                        <p class="text-sm text-secondary-light">Dibuat {{ $order->created_at->format('d M Y, H:i') }}</p>
                    </div>
                    <span class="px-4 py-2 rounded-full text-sm font-semibold flex items-center gap-2 {{ $s['class'] }}">
                        <iconify-icon icon="{{ $s['icon'] }}"></iconify-icon>
                        {{ $s['label'] }}
                    </span>
                </div>
                @if($isRelokasi)
                    <div class="mt-2 flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                            🚚 Relokasi — {{ $isDiffLoc ? 'Beda Lokasi' : '1 Lokasi' }}
                        </span>
                        @if($order->split_technician)
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-700">
                                👥 2 Teknisi Berbeda
                            </span>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Customer --}}
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-3 px-5">
                <h6 class="font-semibold text-sm mb-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:user" class="text-primary-600"></iconify-icon> Customer
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
                        <p class="text-sm text-secondary-light">{{ $order->phone?->phone_number }}</p>
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
                {{-- Jadwal --}}
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

                {{-- Alamat Asal (relokasi beda lokasi) --}}
                @if($isRelokasi && $isDiffLoc && $order->originAddress)
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 bg-warning-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                        <iconify-icon icon="lucide:map-pin-off" class="text-warning-600"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-light">Lokasi Asal (Bongkar)</p>
                        <p class="font-semibold text-sm">{{ $order->originAddress->label }}</p>
                        <p class="text-sm text-secondary-light">{{ $order->originAddress->formatted_address }}</p>
                    </div>
                </div>
                @endif

                {{-- Alamat Tujuan --}}
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                        <iconify-icon icon="lucide:map-pin" class="text-primary-600"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-light">{{ $isRelokasi && $isDiffLoc ? 'Lokasi Tujuan (Pasang)' : 'Lokasi' }}</p>
                        <p class="font-semibold text-sm">{{ $order->address?->label }}</p>
                        <p class="text-sm text-secondary-light">{{ $order->address?->formatted_address }}</p>
                        @if($order->address?->notes)
                            <p class="text-sm text-warning-600 mt-1">⚠️ {{ $order->address->notes }}</p>
                        @endif
                    </div>
                </div>

                {{-- Peta --}}
                @if($order->address?->latitude && $order->address?->longitude)
                <div>
                    <p class="text-xs text-secondary-light mb-2">Peta Lokasi Tujuan</p>
                    <div id="map" class="w-full rounded-lg overflow-hidden border border-neutral-200" style="height:220px;z-index:0;"></div>
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

                    <div class="pt-2 space-y-1">
                        @if($order->discount_amount > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-secondary-light">Diskon</span>
                            <span class="text-success-600">- Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($order->apartment_surcharge > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-secondary-light">Biaya Apartemen</span>
                            <span>Rp {{ number_format($order->apartment_surcharge, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($needTransport && $order->transport_fee > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-secondary-light flex items-center gap-1">
                                <iconify-icon icon="lucide:truck"></iconify-icon> Biaya Transportasi
                            </span>
                            <span class="text-orange-600 font-medium">Rp {{ number_format($order->transport_fee, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between font-bold text-base pt-2 border-t border-neutral-200 dark:border-neutral-600">
                            <span>Total</span>
                            <span class="text-primary-600">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ===== KOLOM KANAN ===== --}}
    <div class="col-span-12 lg:col-span-4 flex flex-col gap-6">

        {{-- Set Biaya Transportasi (relokasi beda lokasi, belum diset) --}}
        @if($needTransport && in_array($order->status, ['pending_transport_fee']))
        <div class="card border-0 border-l-4 border-orange-400">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-3 px-5">
                <h6 class="font-semibold text-sm mb-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:truck" class="text-orange-500"></iconify-icon>
                    Set Biaya Transportasi
                </h6>
            </div>
            <div class="card-body p-5">
                <p class="text-sm text-secondary-light mb-4">
                    Tentukan biaya transportasi berdasarkan jarak antara lokasi asal dan tujuan.
                    Customer akan dikonfirmasi sebelum melanjutkan pembayaran.
                </p>
                <form action="{{ route('orders.setTransportFee', $order) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                            Biaya Transportasi (Rp) <span class="text-danger-600">*</span>
                        </label>
                        <input type="number" name="transport_fee" min="0" step="1000"
                            class="form-control radius-8"
                            placeholder="Contoh: 50000" required>
                        <p class="text-xs text-secondary-light mt-1">Referensi: estimasi biaya Lalamove/Grab Express</p>
                    </div>
                    <button type="submit" class="btn btn-warning-600 w-full flex items-center justify-center gap-2">
                        <iconify-icon icon="lucide:send"></iconify-icon>
                        Kirim ke Customer
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Info transport fee sudah diset, menunggu customer --}}
        @if($needTransport && $order->status === 'pending_transport_fee_set')
        <div class="card border-0">
            <div class="card-body p-5">
                <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 text-center">
                    <iconify-icon icon="lucide:clock" class="text-purple-500 text-3xl mb-2"></iconify-icon>
                    <p class="font-semibold text-purple-700">Menunggu Konfirmasi Customer</p>
                    <p class="text-sm text-purple-600 mt-1">
                        Biaya transportasi <strong>Rp {{ number_format($order->transport_fee, 0, ',', '.') }}</strong>
                        sudah dikirim ke customer.
                    </p>
                </div>
            </div>
        </div>
        @endif

        {{-- Assign Teknisi --}}
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-3 px-5">
                <h6 class="font-semibold text-sm mb-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:user-check" class="text-primary-600"></iconify-icon>
                    {{ $isRelokasi && $isDiffLoc ? 'Teknisi Bongkar' : 'Teknisi' }}
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
                        </div>
                    </div>
                    <div class="bg-success-50 text-success-600 px-3 py-2 rounded-lg text-sm flex items-center gap-2">
                        <iconify-icon icon="lucide:check-circle"></iconify-icon>
                        Sudah di-assign
                    </div>
                @else
                    <div class="bg-warning-50 text-warning-600 px-3 py-2 rounded-lg text-sm flex items-center gap-2 mb-4">
                        <iconify-icon icon="lucide:alert-triangle"></iconify-icon>
                        Belum ada teknisi
                    </div>

                    @if($order->status === 'confirmed')
                        @if($technicians->isEmpty())
                            <div class="bg-danger-50 text-danger-600 px-3 py-2 rounded-lg text-sm">
                                Tidak ada teknisi tersedia
                            </div>
                        @else
                        <form action="{{ route('orders.assign', $order) }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                    Pilih Teknisi {{ $isRelokasi && $isDiffLoc ? '(Bongkar)' : '' }}
                                    <span class="text-danger-600">*</span>
                                </label>
                                <select name="technician_id" class="form-control radius-8" required>
                                    <option value="">-- Pilih Teknisi --</option>
                                    @foreach($technicians as $tech)
                                        <option value="{{ $tech->id }}">
                                            {{ $tech->user->name }} · {{ ucfirst($tech->grade) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Opsi 2 teknisi (relokasi beda lokasi) --}}
                            @if($isRelokasi && $isDiffLoc)
                            <div>
                                <div class="flex items-center gap-2 mb-3">
                                    <input type="checkbox" name="split_technician" value="1" id="split_tech"
                                        class="w-4 h-4" onchange="toggleSecondTech(this)">
                                    <label for="split_tech" class="text-sm font-medium cursor-pointer">
                                        Gunakan teknisi berbeda untuk pasang
                                    </label>
                                </div>
                                <div id="second_tech_section" style="display:none">
                                    <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                        Teknisi Pasang <span class="text-danger-600">*</span>
                                    </label>
                                    <select name="second_technician_id" class="form-control radius-8">
                                        <option value="">-- Pilih Teknisi Pasang --</option>
                                        @foreach($technicians as $tech)
                                            <option value="{{ $tech->id }}">
                                                {{ $tech->user->name }} · {{ ucfirst($tech->grade) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @endif

                            <div>
                                <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                    Catatan untuk Teknisi
                                </label>
                                <textarea name="notes" class="form-control radius-8" rows="2"
                                    placeholder="Catatan khusus...">{{ old('notes') }}</textarea>
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

        {{-- Teknisi Pasang (relokasi beda lokasi, 2 teknisi) --}}
        @if($isRelokasi && $isDiffLoc && $order->split_technician)
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-3 px-5">
                <h6 class="font-semibold text-sm mb-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:user-check" class="text-purple-600"></iconify-icon>
                    Teknisi Pasang
                </h6>
            </div>
            <div class="card-body p-5">
                @if($order->secondTechnician)
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-xl font-bold">
                            {{ strtoupper(substr($order->secondTechnician->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-bold">{{ $order->secondTechnician->user->name }}</p>
                            <p class="text-sm text-secondary-light capitalize">{{ $order->secondTechnician->grade }}</p>
                        </div>
                    </div>
                    <div class="bg-purple-50 text-purple-600 px-3 py-2 rounded-lg text-sm flex items-center gap-2">
                        <iconify-icon icon="lucide:check-circle"></iconify-icon>
                        Teknisi Pasang Assigned
                    </div>
                @else
                    <div class="bg-warning-50 text-warning-600 px-3 py-2 rounded-lg text-sm">
                        Belum ada teknisi pasang
                    </div>
                @endif
            </div>
        </div>
        @endif

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
                        <span class="bg-success-100 text-success-600 px-3 py-1 rounded-full text-xs font-semibold">✓ Lunas</span>
                    @else
                        <span class="bg-danger-100 text-danger-600 px-3 py-1 rounded-full text-xs font-semibold">Belum Bayar</span>
                    @endif
                </div>
                @if($order->paid_at)
                <div class="flex justify-between items-center">
                    <span class="text-sm text-secondary-light">Dibayar</span>
                    <span class="text-sm font-medium">{{ \Carbon\Carbon::parse($order->paid_at)->format('d M Y H:i') }}</span>
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
    L.marker([lat, lng]).addTo(map)
        .bindPopup(`<strong>{{ $order->address?->label }}</strong><br>{{ $order->address?->formatted_address }}`)
        .openPopup();
});
</script>
@endif

<script>
function toggleSecondTech(checkbox) {
    document.getElementById('second_tech_section').style.display =
        checkbox.checked ? 'block' : 'none';
}
</script>
@endpush
@endsection