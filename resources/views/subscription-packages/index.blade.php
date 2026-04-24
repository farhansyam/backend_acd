@extends('layouts.app')
@section('title', 'Paket Cuci Langganan')
@section('page-title', 'Paket Cuci Langganan')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">Paket Langganan</li>
@endsection

@push('styles')
<style>
    /* ── Animasi masuk ── */
    @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .anim-in {
        animation: fadeSlideUp .38s ease both;
    }
    .anim-in-1 { animation-delay: .05s; }
    .anim-in-2 { animation-delay: .12s; }
    .anim-in-3 { animation-delay: .19s; }

    /* ── Kartu paket ── */
    .package-card {
        border-radius: 14px;
        border: 1.5px solid transparent;
        background: #fff;
        transition: box-shadow .22s ease, border-color .22s ease, transform .22s ease;
        position: relative;
        overflow: hidden;
    }
    .dark .package-card {
        background: #2a2a2a;
    }
    .package-card:hover {
        box-shadow: 0 8px 28px -4px rgba(0,0,0,.12);
        transform: translateY(-2px);
    }
    .package-card.type-hemat    { border-color: #bfdbfe; }
    .package-card.type-rutin    { border-color: #fde68a; }
    .package-card.type-intensif { border-color: #fecaca; }

    /* strip warna kiri */
    .package-card::before {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 4px;
        border-radius: 14px 0 0 14px;
    }
    .package-card.type-hemat::before    { background: #3b82f6; }
    .package-card.type-rutin::before    { background: #f59e0b; }
    .package-card.type-intensif::before { background: #ef4444; }

    /* ── Badge diskon ── */
    .discount-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 999px;
        letter-spacing: .3px;
    }

    /* ── Price preview box ── */
    #pricePreview {
        border-left: 3px solid #22c55e;
        border-radius: 8px;
    }

    /* ── Tombol aksi ── */
    .action-btn {
        width: 34px; height: 34px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: filter .15s, transform .15s;
        cursor: pointer;
        border: none;
    }
    .action-btn:hover {
        filter: brightness(0.9);
        transform: scale(1.08);
    }

    /* ── Stat pill ── */
    .stat-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        padding: 3px 10px;
        border-radius: 99px;
        background: #f3f4f6;
        color: #6b7280;
        font-weight: 500;
    }
    .dark .stat-pill {
        background: #374151;
        color: #9ca3af;
    }

    /* ── Form card sticky ── */
    @media (min-width: 1024px) {
        .form-sticky { position: sticky; top: 80px; }
    }

    /* ── Input focus ring ── */
    .form-control:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(59,130,246,.18);
        border-color: #3b82f6;
    }

    /* ── Empty state ── */
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 64px 24px;
    }
    .empty-icon-wrap {
        width: 72px; height: 72px;
        border-radius: 50%;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
    }
    .dark .empty-icon-wrap { background: #374151; }
</style>
@endpush

@section('content')

{{-- ── Flash Messages ── --}}
@if(session('success'))
    <div class="flex items-center gap-3 bg-success-100 text-success-700 px-5 py-3.5 rounded-xl mb-5 anim-in shadow-sm">
        <iconify-icon icon="lucide:check-circle-2" class="text-xl flex-shrink-0"></iconify-icon>
        <span class="text-sm font-medium">{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="flex items-center gap-3 bg-danger-100 text-danger-700 px-5 py-3.5 rounded-xl mb-5 anim-in shadow-sm">
        <iconify-icon icon="lucide:alert-circle" class="text-xl flex-shrink-0"></iconify-icon>
        <span class="text-sm font-medium">{{ session('error') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="flex items-start gap-3 bg-danger-100 text-danger-700 px-5 py-3.5 rounded-xl mb-5 anim-in shadow-sm">
        <iconify-icon icon="lucide:x-octagon" class="text-xl flex-shrink-0 mt-0.5"></iconify-icon>
        <ul class="text-sm font-medium list-disc pl-1 space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-2">

    {{-- ═══════════════════════════════════════
         FORM TAMBAH / EDIT
    ════════════════════════════════════════ --}}
    <div class="lg:col-span-1 anim-in anim-in-1">
        <div class="card border-0 shadow-sm rounded-2xl overflow-hidden form-sticky">

            {{-- Header form --}}
            <div class="card-header flex items-center gap-3 border-b border-neutral-100 dark:border-neutral-600
                        bg-gradient-to-r from-primary-600 to-primary-500 py-4 px-6">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                    <iconify-icon icon="lucide:package-plus" class="text-white text-lg"></iconify-icon>
                </div>
                <h6 class="text-base font-semibold mb-0 text-white" id="formTitle">Tambah Paket</h6>
            </div>

            <div class="card-body p-6">
                <form id="packageForm" action="{{ route('subscription-packages.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">

                    <div class="flex flex-col gap-4">

                        {{-- Tipe Paket --}}
                        <div>
                            <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                Tipe Paket <span class="text-danger-600">*</span>
                            </label>
                            <select name="type" id="fieldType" class="form-control radius-8" required
                                    onchange="handleTypeChange(this.value)">
                                <option value="">— Pilih tipe —</option>
                                <option value="hemat">🔵 Hemat &nbsp;(6 bln sekali)</option>
                                <option value="rutin">🟡 Rutin &nbsp;(3 bln sekali)</option>
                                <option value="intensif">🔴 Intensif &nbsp;(1 bln sekali)</option>
                            </select>
                            <p class="text-xs text-secondary-light mt-1" id="typeNote"></p>
                        </div>

                        {{-- Nama Paket --}}
                        <div>
                            <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                Nama Paket <span class="text-danger-600">*</span>
                            </label>
                            <input type="text" name="name" id="fieldName"
                                   class="form-control radius-8"
                                   placeholder="Contoh: Paket Hemat Silver" required>
                        </div>

                        {{-- Interval & Sesi --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                    Interval <span class="text-danger-600">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" name="interval_months" id="fieldInterval"
                                           class="form-control radius-8 pr-14"
                                           min="1" max="12" placeholder="6" required>
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-secondary-light font-medium pointer-events-none">bln</span>
                                </div>
                                <p class="text-xs text-secondary-light mt-1">Jarak antar sesi</p>
                            </div>
                            <div>
                                <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                    Total Sesi <span class="text-danger-600">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" name="total_sessions" id="fieldSessions"
                                           class="form-control radius-8 pr-14"
                                           min="1" max="24" placeholder="2" required>
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-secondary-light font-medium pointer-events-none">/thn</span>
                                </div>
                            </div>
                        </div>

                        {{-- Price Multiplier --}}
                        <div>
                            <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                Price Multiplier <span class="text-danger-600">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" name="price_multiplier" id="fieldMultiplier"
                                       class="form-control radius-8"
                                       min="0.1" max="1" step="0.01" placeholder="0.90" required
                                       oninput="updateDiscountPreview()">
                            </div>
                            <p class="text-xs text-secondary-light mt-1 flex items-center gap-1">
                                <span>0.1 – 1.0 &nbsp;·&nbsp; 1.0 = harga normal</span>
                                <span id="discountPreview" class="text-success-600 font-semibold"></span>
                            </p>
                        </div>

                        {{-- Preview Harga --}}
                        <div id="pricePreview"
                             class="bg-success-50 dark:bg-neutral-700 rounded-xl px-4 py-3"
                             style="display:none">
                            <p class="text-xs text-secondary-light mb-1 font-semibold uppercase tracking-wide">
                                Preview &nbsp;(asumsi 1PK = Rp 100.000)
                            </p>
                            <p class="text-base font-bold text-success-600" id="previewFinal">-</p>
                            <p class="text-xs text-secondary-light line-through" id="previewNormal">-</p>
                        </div>

                        {{-- Deskripsi --}}
                        <div>
                            <label class="form-label fw-semibold text-primary-light text-sm mb-2">Deskripsi</label>
                            <textarea name="description" id="fieldDescription"
                                      class="form-control radius-8" rows="2"
                                      placeholder="Deskripsi singkat paket..."></textarea>
                        </div>

                        {{-- Status --}}
                        <div>
                            <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                Status <span class="text-danger-600">*</span>
                            </label>
                            <select name="is_active" id="fieldIsActive" class="form-control radius-8" required>
                                <option value="1">✅ Aktif</option>
                                <option value="0">⏸ Nonaktif</option>
                            </select>
                        </div>

                        {{-- Tombol --}}
                        <div class="flex items-center gap-3 pt-1">
                            <button type="submit"
                                    class="btn btn-primary-600 flex-1 flex items-center justify-center gap-2"
                                    id="submitBtn">
                                <iconify-icon icon="lucide:save" id="submitIcon"></iconify-icon>
                                <span>Simpan</span>
                            </button>
                            <button type="button"
                                    class="btn btn-neutral-200 flex items-center gap-2"
                                    id="cancelEditBtn"
                                    style="display:none"
                                    onclick="resetForm()">
                                <iconify-icon icon="lucide:x"></iconify-icon>
                                Batal
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════
         DAFTAR PAKET
    ════════════════════════════════════════ --}}
    <div class="lg:col-span-2 anim-in anim-in-2">
        <div class="card border-0 shadow-sm rounded-2xl overflow-hidden">

            {{-- Header daftar --}}
            <div class="card-header flex items-center justify-between
                        border-b border-neutral-100 dark:border-neutral-600
                        bg-white dark:bg-neutral-700 py-4 px-6">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                        <iconify-icon icon="lucide:layers" class="text-primary-600 text-lg"></iconify-icon>
                    </div>
                    <h6 class="text-base font-semibold mb-0">Daftar Paket</h6>
                </div>
                <span class="text-xs text-secondary-light bg-neutral-100 dark:bg-neutral-600 px-3 py-1 rounded-full font-medium">
                    {{ $packages->count() }} paket
                </span>
            </div>

            <div class="card-body p-5">

                @forelse($packages as $i => $package)
                @php
                    $typeConfig = match($package->type) {
                        'hemat'    => [
                            'bg'      => 'bg-info-100',
                            'text'    => 'text-info-600',
                            'badge'   => 'bg-blue-100 text-blue-700',
                            'strip'   => 'type-hemat',
                            'icon'    => 'lucide:piggy-bank',
                            'pillbg'  => 'bg-blue-50 text-blue-600',
                        ],
                        'rutin'    => [
                            'bg'      => 'bg-warning-100',
                            'text'    => 'text-warning-600',
                            'badge'   => 'bg-yellow-100 text-yellow-700',
                            'strip'   => 'type-rutin',
                            'icon'    => 'lucide:calendar-clock',
                            'pillbg'  => 'bg-yellow-50 text-yellow-600',
                        ],
                        'intensif' => [
                            'bg'      => 'bg-danger-100',
                            'text'    => 'text-danger-600',
                            'badge'   => 'bg-red-100 text-red-700',
                            'strip'   => 'type-intensif',
                            'icon'    => 'lucide:zap',
                            'pillbg'  => 'bg-red-50 text-red-600',
                        ],
                        default    => [
                            'bg'      => 'bg-neutral-100',
                            'text'    => 'text-neutral-600',
                            'badge'   => 'bg-neutral-100 text-neutral-600',
                            'strip'   => '',
                            'icon'    => 'lucide:package',
                            'pillbg'  => 'bg-neutral-100 text-neutral-500',
                        ],
                    };
                    $discountPct   = round((1 - $package->price_multiplier) * 100);
                    $exampleTotal  = 100000 * $package->total_sessions * $package->price_multiplier;
                    $exampleNormal = 100000 * $package->total_sessions;
                @endphp

                <div class="package-card {{ $typeConfig['strip'] }} p-5 mb-4 last:mb-0 anim-in"
                     style="animation-delay: {{ ($i * 0.07) + 0.2 }}s">

                    <div class="flex items-start justify-between gap-4">

                        {{-- Ikon tipe --}}
                        <div class="flex-shrink-0 w-11 h-11 rounded-xl {{ $typeConfig['bg'] }}
                                    flex items-center justify-center mt-0.5">
                            <iconify-icon icon="{{ $typeConfig['icon'] }}"
                                          class="{{ $typeConfig['text'] }} text-xl"></iconify-icon>
                        </div>

                        {{-- Konten utama --}}
                        <div class="flex-1 min-w-0">

                            {{-- Baris judul & badge --}}
                            <div class="flex items-center gap-2 flex-wrap mb-2">
                                <span class="discount-badge {{ $typeConfig['badge'] }}">
                                    {{ ucfirst($package->type) }}
                                </span>
                                <h6 class="font-semibold text-sm mb-0 truncate">{{ $package->name }}</h6>
                                @if($package->is_active)
                                    <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full
                                                 bg-success-100 text-success-600 font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-success-500 inline-block"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full
                                                 bg-neutral-100 text-neutral-400 font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-neutral-300 inline-block"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </div>

                            {{-- Stat pills --}}
                            <div class="flex flex-wrap gap-2 mb-3">
                                <span class="stat-pill">
                                    <iconify-icon icon="lucide:calendar"></iconify-icon>
                                    Interval {{ $package->interval_months }} bln
                                </span>
                                <span class="stat-pill">
                                    <iconify-icon icon="lucide:repeat-2"></iconify-icon>
                                    {{ $package->total_sessions }}× sesi/tahun
                                </span>
                                @if($discountPct > 0)
                                    <span class="stat-pill {{ $typeConfig['pillbg'] }} font-semibold">
                                        <iconify-icon icon="lucide:tag"></iconify-icon>
                                        Hemat {{ $discountPct }}%
                                    </span>
                                @else
                                    <span class="stat-pill">
                                        <iconify-icon icon="lucide:tag"></iconify-icon>
                                        Harga normal
                                    </span>
                                @endif
                            </div>

                            {{-- Contoh harga --}}
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs text-secondary-light">Contoh 1PK @100rb:</span>
                                <span class="text-sm font-bold {{ $typeConfig['text'] }}">
                                    Rp {{ number_format($exampleTotal, 0, ',', '.') }}/tahun
                                </span>
                                <span class="text-xs text-secondary-light line-through">
                                    Rp {{ number_format($exampleNormal, 0, ',', '.') }}
                                </span>
                            </div>

                            @if($package->description)
                                <p class="text-xs text-secondary-light mt-2 leading-relaxed">
                                    {{ $package->description }}
                                </p>
                            @endif
                        </div>

                        {{-- Tombol aksi --}}
                        <div class="flex flex-col gap-2 flex-shrink-0">
                            <button type="button"
                                    class="action-btn bg-warning-100 text-warning-600"
                                    title="Edit paket"
                                    onclick="fillEditForm(
                                        {{ $package->id }},
                                        '{{ $package->type }}',
                                        '{{ addslashes($package->name) }}',
                                        {{ $package->interval_months }},
                                        {{ $package->total_sessions }},
                                        {{ $package->price_multiplier }},
                                        '{{ addslashes($package->description ?? '') }}',
                                        {{ $package->is_active ? 1 : 0 }}
                                    )">
                                <iconify-icon icon="lucide:pencil"></iconify-icon>
                            </button>

                            <form action="{{ route('subscription-packages.toggle', $package) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="action-btn {{ $package->is_active
                                            ? 'bg-neutral-100 text-neutral-400'
                                            : 'bg-success-100 text-success-600' }}"
                                        title="{{ $package->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    <iconify-icon icon="{{ $package->is_active ? 'lucide:eye-off' : 'lucide:eye' }}"></iconify-icon>
                                </button>
                            </form>
                        </div>

                    </div>
                </div>

                @empty
                <div class="empty-state">
                    <div class="empty-icon-wrap">
                        <iconify-icon icon="lucide:package-open" class="text-neutral-400"></iconify-icon>
                    </div>
                    <p class="text-sm text-secondary-light text-center max-w-xs leading-relaxed">
                        Belum ada paket langganan.<br>
                        Isi form di sebelah kiri untuk menambahkan paket baru.
                    </p>
                </div>
                @endforelse

            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
// ─── Konfigurasi tipe paket ───────────────────────────────────────────────────
const TYPE_NOTES = {
    hemat:    '💡 Cuci 2× setahun, jarak 6 bulan — cocok untuk penggunaan ringan.',
    rutin:    '🔄 Cuci 4× setahun, jarak 3 bulan — paling banyak dipilih.',
    intensif: '⚡ Cuci 12× setahun, tiap bulan — untuk lingkungan berdebu.',
};

function handleTypeChange(val) {
    const note = document.getElementById('typeNote');
    note.textContent = TYPE_NOTES[val] ?? '';
}

// ─── Isi form untuk edit ──────────────────────────────────────────────────────
function fillEditForm(id, type, name, interval, sessions, multiplier, description, isActive) {
    const form    = document.getElementById('packageForm');
    const baseUrl = '{{ url("subscription-packages") }}';

    form.action = `${baseUrl}/${id}`;
    document.getElementById('formMethod').value       = 'PUT';
    document.getElementById('formTitle').textContent  = 'Edit Paket';
    document.getElementById('submitBtn').querySelector('span').textContent = 'Update';
    document.getElementById('submitIcon').setAttribute('icon', 'lucide:save');
    document.getElementById('cancelEditBtn').style.display = 'inline-flex';

    // Isi field
    const fieldType = document.getElementById('fieldType');
    fieldType.value    = type;
    fieldType.disabled = true;   // tipe tidak bisa diubah saat edit
    handleTypeChange(type);

    document.getElementById('fieldName').value        = name;
    document.getElementById('fieldInterval').value    = interval;
    document.getElementById('fieldSessions').value    = sessions;
    document.getElementById('fieldMultiplier').value  = multiplier;
    document.getElementById('fieldDescription').value = description;
    document.getElementById('fieldIsActive').value    = isActive;

    updateDiscountPreview();

    // Scroll ke form
    form.closest('.card').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ─── Reset ke mode tambah ─────────────────────────────────────────────────────
function resetForm() {
    const form = document.getElementById('packageForm');
    form.action = '{{ route("subscription-packages.store") }}';
    form.reset();

    document.getElementById('formMethod').value       = 'POST';
    document.getElementById('formTitle').textContent  = 'Tambah Paket';
    document.getElementById('submitBtn').querySelector('span').textContent = 'Simpan';
    document.getElementById('cancelEditBtn').style.display = 'none';
    document.getElementById('fieldType').disabled     = false;
    document.getElementById('discountPreview').textContent = '';
    document.getElementById('pricePreview').style.display  = 'none';
    document.getElementById('typeNote').textContent   = '';
}

// ─── Live preview diskon ──────────────────────────────────────────────────────
function updateDiscountPreview() {
    const multiplier = parseFloat(document.getElementById('fieldMultiplier').value);
    const sessions   = parseInt(document.getElementById('fieldSessions').value) || 0;
    const preview    = document.getElementById('discountPreview');
    const priceBox   = document.getElementById('pricePreview');

    if (!isNaN(multiplier) && multiplier > 0 && multiplier <= 1) {
        const pct = Math.round((1 - multiplier) * 100);
        preview.textContent = pct > 0 ? `· hemat ${pct}%` : '· harga normal';

        if (sessions > 0) {
            const normal = 100000 * sessions;
            const final  = Math.round(normal * multiplier);
            document.getElementById('previewNormal').textContent =
                'Normal: Rp ' + normal.toLocaleString('id-ID');
            document.getElementById('previewFinal').textContent =
                'Rp ' + final.toLocaleString('id-ID') + ' / tahun';
            priceBox.style.display = 'block';
        } else {
            priceBox.style.display = 'none';
        }
    } else {
        preview.textContent        = '';
        priceBox.style.display     = 'none';
    }
}

document.getElementById('fieldSessions').addEventListener('input', updateDiscountPreview);
</script>
@endpush

@endsection