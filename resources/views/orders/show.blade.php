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
    $isPerbaikan   = (bool) $order->is_perbaikan;
    $isSurvey      = $isPerbaikan && $order->perbaikan_phase === 'survey';
    $isPhase2      = $isPerbaikan && $order->perbaikan_phase === 'phase2';

    // Order survey terkait (jika ini fase2, ambil survey order-nya)
    $surveyOrder = $isPhase2 && $order->survey_order_id
        ? \App\Models\Order::with('surveyReport.technician.user')->find($order->survey_order_id)
        : null;

    // Order fase2 terkait (jika ini survey dan sudah ada fase2)
    $phase2Order = $isSurvey && $order->phase2_order_id
        ? \App\Models\Order::with('items.bpService.serviceType')->find($order->phase2_order_id)
        : null;

    // Survey report
    $surveyReport = $isSurvey
        ? $order->surveyReport
        : ($isPhase2 && $surveyOrder ? $surveyOrder->surveyReport : null);

    $statusMap = [
        'pending'                   => ['label' => 'Menunggu Konfirmasi',         'class' => 'bg-warning-100 text-warning-600',   'icon' => 'lucide:clock'],
        'pending_transport_fee'     => ['label' => 'Menunggu Biaya Transportasi', 'class' => 'bg-orange-100 text-orange-600',     'icon' => 'lucide:truck'],
        'pending_transport_fee_set' => ['label' => 'Menunggu Konfirmasi Customer','class' => 'bg-purple-100 text-purple-600',     'icon' => 'lucide:user-check'],
        'confirmed'                 => ['label' => 'Dikonfirmasi',                'class' => 'bg-info-100 text-info-600',         'icon' => 'lucide:check-circle'],
        'in_progress'               => ['label' => 'Sedang Dikerjakan',           'class' => 'bg-warning-100 text-warning-600',   'icon' => 'lucide:wrench'],
        'survey_in_progress'        => ['label' => 'Survei Berlangsung',          'class' => 'bg-indigo-100 text-indigo-600',     'icon' => 'lucide:search'],
        'waiting_customer_response' => ['label' => 'Menunggu Keputusan Customer', 'class' => 'bg-purple-100 text-purple-600',     'icon' => 'lucide:help-circle'],
        'waiting_confirmation'      => ['label' => 'Menunggu Konfirmasi',         'class' => 'bg-purple-100 text-purple-600',     'icon' => 'lucide:clock'],
        'completed'                 => ['label' => 'Selesai',                     'class' => 'bg-success-100 text-success-600',   'icon' => 'lucide:badge-check'],
        'warranty'                  => ['label' => 'Masa Garansi',                'class' => 'bg-teal-100 text-teal-600',         'icon' => 'lucide:shield'],
        'complained'                => ['label' => 'Dikomplain',                  'class' => 'bg-danger-100 text-danger-600',     'icon' => 'lucide:alert-triangle'],
        'cancelled'                 => ['label' => 'Dibatalkan',                  'class' => 'bg-neutral-100 text-neutral-500',   'icon' => 'lucide:x-circle'],
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

{{-- Banner relokasi --}}
@if($needTransport && $order->status === 'pending_transport_fee')
    <div class="bg-orange-50 border border-orange-200 rounded-xl px-5 py-4 mb-6 flex items-start gap-3">
        <iconify-icon icon="lucide:truck" class="text-orange-500 text-xl mt-0.5"></iconify-icon>
        <div>
            <p class="font-semibold text-orange-700">Order Relokasi — Beda Lokasi</p>
            <p class="text-sm text-orange-600 mt-1">Customer menunggu Anda menentukan biaya transportasi.</p>
        </div>
    </div>
@endif

{{-- Banner perbaikan --}}
@if($isPerbaikan)
    <div class="bg-purple-50 border border-purple-200 rounded-xl px-5 py-4 mb-6 flex items-start gap-3">
        <iconify-icon icon="lucide:wrench" class="text-purple-500 text-xl mt-0.5"></iconify-icon>
        <div class="flex-1">
            <p class="font-semibold text-purple-700">
                Service Perbaikan —
                @if($isSurvey) Fase 1: Survey
                @else Fase 2: {{ $surveyReport?->rekomendasi === 'cuci_unit' ? 'Cuci Unit' : 'Perbaikan' }}
                @endif
            </p>
            <p class="text-sm text-purple-600 mt-1">
                @if($isSurvey)
                    Teknisi akan datang untuk survey kondisi AC. Hasil survey akan menentukan tindakan selanjutnya.
                @else
                    Lanjutan dari order survey
                    @if($surveyOrder)
                        <a href="{{ route('orders.show', $surveyOrder->id) }}" class="underline font-medium">#{{ $surveyOrder->id }}</a>.
                    @endif
                @endif
            </p>
            @if($isSurvey && $phase2Order)
                <p class="text-sm text-purple-600 mt-1">
                    Customer sudah lanjut ke
                    <a href="{{ route('orders.show', $phase2Order->id) }}" class="underline font-medium">Order Fase 2 #{{ $phase2Order->id }}</a>.
                </p>
            @endif
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
                <div class="flex flex-wrap gap-2 mt-2">
                    @if($isRelokasi)
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                            🚚 Relokasi — {{ $isDiffLoc ? 'Beda Lokasi' : '1 Lokasi' }}
                        </span>
                        @if($order->split_technician)
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-700">
                                👥 2 Teknisi
                            </span>
                        @endif
                    @endif
                    @if($isPerbaikan)
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-700">
                            🔧 Perbaikan — {{ $isSurvey ? 'Fase 1 Survey' : 'Fase 2' }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Survey Report (jika ada) --}}
        @if($surveyReport)
        <div class="card border-0 border-l-4 border-indigo-400">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-3 px-5">
                <h6 class="font-semibold text-sm mb-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:clipboard-list" class="text-indigo-600"></iconify-icon>
                    Hasil Report Survey
                </h6>
            </div>
            <div class="card-body p-5 space-y-4">

                {{-- Foto Before/After --}}
                @if($surveyReport->photo_before || $surveyReport->photo_after)
                <div class="grid grid-cols-2 gap-3">
                    @if($surveyReport->photo_before)
                    <div>
                        <p class="text-xs text-secondary-light mb-1 font-medium">📷 Sebelum</p>
                        <img src="{{ asset('storage/' . $surveyReport->photo_before) }}"
                            class="w-full rounded-lg object-cover" style="height:150px;"
                            alt="Before">
                    </div>
                    @endif
                    @if($surveyReport->photo_after)
                    <div>
                        <p class="text-xs text-secondary-light mb-1 font-medium">📷 Sesudah</p>
                        <img src="{{ asset('storage/' . $surveyReport->photo_after) }}"
                            class="w-full rounded-lg object-cover" style="height:150px;"
                            alt="After">
                    </div>
                    @endif
                </div>
                @endif

                {{-- Detail --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-secondary-light font-medium mb-1">Kondisi Unit</p>
                        @php
                            $kondisiClass = match($surveyReport->kondisi_unit) {
                                'rusak' => 'bg-danger-100 text-danger-600',
                                'kotor' => 'bg-warning-100 text-warning-600',
                                default => 'bg-success-100 text-success-600',
                            };
                            $kondisiLabel = match($surveyReport->kondisi_unit) {
                                'rusak' => 'Rusak',
                                'kotor' => 'Kotor',
                                default => 'Normal',
                            };
                        @endphp
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $kondisiClass }}">
                            {{ $kondisiLabel }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-light font-medium mb-1">Rekomendasi</p>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-primary-100 text-primary-600">
                            {{ $surveyReport->rekomendasi === 'cuci_unit' ? '🫧 Cuci Unit' : '🔩 Perbaikan' }}
                        </span>
                    </div>
                </div>

                {{-- Bagian bermasalah --}}
                @if($surveyReport->bagian_bermasalah && count($surveyReport->bagian_bermasalah) > 0)
                <div>
                    <p class="text-xs text-secondary-light font-medium mb-2">Bagian Bermasalah</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach($surveyReport->bagian_bermasalah as $bagian)
                            <span class="px-2 py-1 bg-danger-50 text-danger-600 rounded text-xs">{{ $bagian }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Catatan --}}
                @if($surveyReport->catatan)
                <div>
                    <p class="text-xs text-secondary-light font-medium mb-1">Catatan Teknisi</p>
                    <p class="text-sm bg-neutral-50 rounded-lg p-3">{{ $surveyReport->catatan }}</p>
                </div>
                @endif

                {{-- Respon customer --}}
                <div class="border-t border-neutral-100 pt-3">
                    <p class="text-xs text-secondary-light font-medium mb-2">Keputusan Customer</p>
                    @if($surveyReport->customer_response)
                        @if($surveyReport->customer_response === 'lanjut')
                            <div class="flex items-center gap-2 text-success-600 font-medium text-sm">
                                <iconify-icon icon="lucide:check-circle"></iconify-icon>
                                Lanjut ke Fase 2
                                @if($surveyReport->responded_at)
                                    <span class="text-secondary-light font-normal text-xs">
                                        · {{ \Carbon\Carbon::parse($surveyReport->responded_at)->format('d M Y H:i') }}
                                    </span>
                                @endif
                            </div>
                            @if($phase2Order || $surveyOrder)
                                @php $linkedOrder = $phase2Order ?? ($isPhase2 ? $order : null); @endphp
                                @if($linkedOrder)
                                <a href="{{ route('orders.show', $linkedOrder->id) }}"
                                   class="mt-2 inline-flex items-center gap-1 text-xs text-primary-600 underline">
                                    <iconify-icon icon="lucide:external-link"></iconify-icon>
                                    Lihat Order Fase 2 #{{ $linkedOrder->id }}
                                </a>
                                @endif
                            @endif
                        @else
                            <div class="flex items-center gap-2 text-danger-600 font-medium text-sm">
                                <iconify-icon icon="lucide:x-circle"></iconify-icon>
                                Tidak Lanjut
                                @if($surveyReport->responded_at)
                                    <span class="text-secondary-light font-normal text-xs">
                                        · {{ \Carbon\Carbon::parse($surveyReport->responded_at)->format('d M Y H:i') }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    @else
                        <span class="text-xs text-warning-600 flex items-center gap-1">
                            <iconify-icon icon="lucide:clock"></iconify-icon>
                            Menunggu keputusan customer
                        </span>
                    @endif
                </div>

            </div>
        </div>
        @endif

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

                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                        <iconify-icon icon="lucide:map-pin" class="text-primary-600"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-light">
                            {{ $isRelokasi && $isDiffLoc ? 'Lokasi Tujuan (Pasang)' : 'Lokasi' }}
                        </p>
                        <p class="font-semibold text-sm">{{ $order->address?->label }}</p>
                        <p class="text-sm text-secondary-light">{{ $order->address?->formatted_address }}</p>
                        @if($order->address?->notes)
                            <p class="text-sm text-warning-600 mt-1">⚠️ {{ $order->address->notes }}</p>
                        @endif
                    </div>
                </div>

                @if($order->address?->latitude && $order->address?->longitude)
                <div>
                    <p class="text-xs text-secondary-light mb-2">Peta Lokasi</p>
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

                    {{-- Jika ini fase survey dan ada fase2, tampilkan total gabungan --}}
                    @if($isSurvey && $phase2Order)
                    <div class="mt-3 p-3 bg-purple-50 rounded-lg border border-purple-100">
                        <p class="text-xs font-semibold text-purple-700 mb-2">💡 Total Gabungan (Survey + Fase 2)</p>
                        <div class="flex justify-between text-sm">
                            <span class="text-secondary-light">Biaya Survey</span>
                            <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-secondary-light">Biaya Fase 2</span>
                            <span>Rp {{ number_format($phase2Order->total_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between font-bold text-sm mt-2 pt-2 border-t border-purple-200">
                            <span>Total</span>
                            <span class="text-purple-700">Rp {{ number_format($order->total_amount + $phase2Order->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    @endif

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
                            <span>Total Order Ini</span>
                            <span class="text-primary-600">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ===== KOLOM KANAN ===== --}}
    <div class="col-span-12 lg:col-span-4 flex flex-col gap-6">

        {{-- Link order terkait (perbaikan) --}}
        @if($isSurvey && $phase2Order)
        <div class="card border-0 border-l-4 border-purple-400">
            <div class="card-body p-5">
                <p class="text-xs font-semibold text-purple-700 mb-2 flex items-center gap-1">
                    <iconify-icon icon="lucide:link"></iconify-icon> Order Terkait
                </p>
                <a href="{{ route('orders.show', $phase2Order->id) }}"
                   class="flex items-center justify-between p-3 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                    <div>
                        <p class="font-semibold text-sm text-purple-700">Order Fase 2 #{{ $phase2Order->id }}</p>
                        <p class="text-xs text-purple-600">{{ $phase2Order->items->first()?->bpService?->serviceType?->name ?? '-' }}</p>
                    </div>
                    <iconify-icon icon="lucide:arrow-right" class="text-purple-500"></iconify-icon>
                </a>
            </div>
        </div>
        @endif

        @if($isPhase2 && $surveyOrder)
        <div class="card border-0 border-l-4 border-indigo-400">
            <div class="card-body p-5">
                <p class="text-xs font-semibold text-indigo-700 mb-2 flex items-center gap-1">
                    <iconify-icon icon="lucide:link"></iconify-icon> Order Terkait
                </p>
                <a href="{{ route('orders.show', $surveyOrder->id) }}"
                   class="flex items-center justify-between p-3 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition">
                    <div>
                        <p class="font-semibold text-sm text-indigo-700">Order Survey #{{ $surveyOrder->id }}</p>
                        <p class="text-xs text-indigo-600">Fase 1 — Survey</p>
                    </div>
                    <iconify-icon icon="lucide:arrow-right" class="text-indigo-500"></iconify-icon>
                </a>
            </div>
        </div>
        @endif

        {{-- Set Biaya Transportasi --}}
        @if($needTransport && $order->status === 'pending_transport_fee')
        <div class="card border-0 border-l-4 border-orange-400">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-3 px-5">
                <h6 class="font-semibold text-sm mb-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:truck" class="text-orange-500"></iconify-icon>
                    Set Biaya Transportasi
                </h6>
            </div>
            <div class="card-body p-5">
                <form action="{{ route('orders.setTransportFee', $order) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                            Biaya Transportasi (Rp) <span class="text-danger-600">*</span>
                        </label>
                        <input type="number" name="transport_fee" min="0" step="1000"
                            class="form-control radius-8" placeholder="Contoh: 50000" required>
                    </div>
                    <button type="submit" class="btn btn-warning-600 w-full flex items-center justify-center gap-2">
                        <iconify-icon icon="lucide:send"></iconify-icon>
                        Kirim ke Customer
                    </button>
                </form>
            </div>
        </div>
        @endif

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
                            </div>

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
                                    placeholder="Catatan khusus..."></textarea>
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

        {{-- Teknisi Pasang (relokasi 2 teknisi) --}}
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