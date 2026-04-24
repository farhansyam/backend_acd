@extends('layouts.app')
@section('title', 'Paket Cuci Langganan')
@section('page-title', 'Paket Cuci Langganan')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">Paket Langganan</li>
@endsection

@section('content')

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

@if($errors->any())
    <div class="bg-danger-100 text-danger-600 px-4 py-3 rounded mb-4">
        <ul class="mb-0 list-disc pl-4">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">

    {{-- ===== FORM TAMBAH / EDIT ===== --}}
    <div class="lg:col-span-1">
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6">
                <h6 class="text-base font-semibold mb-0" id="formTitle">Tambah Paket</h6>
            </div>
            <div class="card-body p-6">
                <form id="packageForm" action="{{ route('subscription-packages.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <div class="flex flex-col gap-4">

                        <div>
                            <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                Tipe Paket <span class="text-danger-600">*</span>
                            </label>
                            <select name="type" id="fieldType" class="form-control radius-8" required>
                                <option value="">-- Pilih tipe --</option>
                                <option value="hemat">Hemat (6 bln sekali)</option>
                                <option value="rutin">Rutin (3 bln sekali)</option>
                                <option value="intensif">Intensif (1 bln sekali)</option>
                            </select>
                            <p class="text-xs text-secondary-light mt-1" id="typeNote"></p>
                        </div>

                        <div>
                            <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                Nama Paket <span class="text-danger-600">*</span>
                            </label>
                            <input type="text" name="name" id="fieldName"
                                class="form-control radius-8"
                                placeholder="Contoh: Paket Hemat" required>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                    Interval (bulan) <span class="text-danger-600">*</span>
                                </label>
                                <input type="number" name="interval_months" id="fieldInterval"
                                    class="form-control radius-8"
                                    min="1" max="12" placeholder="6" required>
                                <p class="text-xs text-secondary-light mt-1">Jarak antar sesi</p>
                            </div>
                            <div>
                                <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                    Total Sesi <span class="text-danger-600">*</span>
                                </label>
                                <input type="number" name="total_sessions" id="fieldSessions"
                                    class="form-control radius-8"
                                    min="1" max="24" placeholder="2" required>
                                <p class="text-xs text-secondary-light mt-1">Per tahun</p>
                            </div>
                        </div>

                        <div>
                            <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                Price Multiplier <span class="text-danger-600">*</span>
                            </label>
                            <input type="number" name="price_multiplier" id="fieldMultiplier"
                                class="form-control radius-8"
                                min="0.1" max="1" step="0.01" placeholder="0.90" required
                                oninput="updateDiscountPreview()">
                            <p class="text-xs text-secondary-light mt-1">
                                0.1–1.0 · 1.0 = harga normal ·
                                <span id="discountPreview" class="text-success-600 font-medium"></span>
                            </p>
                        </div>

                        {{-- Preview harga --}}
                        <div class="bg-neutral-50 dark:bg-neutral-700 rounded-lg p-3" id="pricePreview" style="display:none">
                            <p class="text-xs text-secondary-light mb-1 font-medium">Preview harga (asumsi cuci 1PK = Rp 100.000)</p>
                            <p class="text-sm font-semibold text-primary-600" id="previewFinal">-</p>
                            <p class="text-xs text-secondary-light line-through" id="previewNormal">-</p>
                        </div>

                        <div>
                            <label class="form-label fw-semibold text-primary-light text-sm mb-2">Deskripsi</label>
                            <textarea name="description" id="fieldDescription"
                                class="form-control radius-8" rows="2"
                                placeholder="Deskripsi singkat..."></textarea>
                        </div>

                        <div>
                            <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                Status <span class="text-danger-600">*</span>
                            </label>
                            <select name="is_active" id="fieldIsActive" class="form-control radius-8" required>
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-3 mt-2">
                            <button type="submit" class="btn btn-primary-600" id="submitBtn">Simpan</button>
                            <button type="button" class="btn btn-neutral-200" id="cancelEditBtn"
                                style="display:none" onclick="resetForm()">Batal Edit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== DAFTAR PAKET ===== --}}
    <div class="lg:col-span-2">
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6">
                <h6 class="text-base font-semibold mb-0">Daftar Paket</h6>
            </div>
            <div class="card-body p-6">
                @forelse($packages as $package)
                @php
                    $typeColor = match($package->type) {
                        'hemat'    => ['bg' => 'bg-info-100',    'text' => 'text-info-600'],
                        'rutin'    => ['bg' => 'bg-warning-100', 'text' => 'text-warning-600'],
                        'intensif' => ['bg' => 'bg-danger-100',  'text' => 'text-danger-600'],
                        default    => ['bg' => 'bg-neutral-100', 'text' => 'text-neutral-600'],
                    };
                    $discountPct = round((1 - $package->price_multiplier) * 100);
                    $exampleTotal = 100000 * $package->total_sessions * $package->price_multiplier;
                    $exampleNormal = 100000 * $package->total_sessions;
                @endphp
                <div class="flex flex-col gap-4 py-4 border-b border-neutral-100 dark:border-neutral-700 last:border-0">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $typeColor['bg'] }} {{ $typeColor['text'] }}">
                                    {{ ucfirst($package->type) }}
                                </span>
                                <h6 class="font-semibold text-sm mb-0">{{ $package->name }}</h6>
                                @if($package->is_active)
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-success-100 text-success-600">Aktif</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-neutral-100 text-neutral-500">Nonaktif</span>
                                @endif
                            </div>

                            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-sm text-secondary-light">
                                <span>
                                    <iconify-icon icon="lucide:calendar"></iconify-icon>
                                    Interval {{ $package->interval_months }} bln
                                </span>
                                <span>
                                    <iconify-icon icon="lucide:repeat"></iconify-icon>
                                    {{ $package->total_sessions }}x sesi/tahun
                                </span>
                                <span class="font-medium {{ $typeColor['text'] }}">
                                    <iconify-icon icon="lucide:tag"></iconify-icon>
                                    Hemat {{ $discountPct }}% (×{{ $package->price_multiplier }})
                                </span>
                            </div>

                            <div class="mt-2 text-xs text-secondary-light">
                                Contoh 1PK @100rb:
                                <span class="font-semibold {{ $typeColor['text'] }}">
                                    Rp {{ number_format($exampleTotal, 0, ',', '.') }}/tahun
                                </span>
                                <span class="line-through ml-1">
                                    Rp {{ number_format($exampleNormal, 0, ',', '.') }}
                                </span>
                            </div>

                            @if($package->description)
                                <p class="text-xs text-secondary-light mt-1">{{ $package->description }}</p>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="flex gap-2 flex-shrink-0">
                            <button type="button"
                                class="w-8 h-8 bg-warning-100 text-warning-600 rounded flex items-center justify-center"
                                title="Edit"
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
                                    class="w-8 h-8 rounded flex items-center justify-center
                                        {{ $package->is_active ? 'bg-neutral-100 text-neutral-500' : 'bg-success-100 text-success-600' }}"
                                    title="{{ $package->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    <iconify-icon icon="{{ $package->is_active ? 'lucide:eye-off' : 'lucide:eye' }}"></iconify-icon>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-10 text-secondary-light">
                    <iconify-icon icon="lucide:package-open" class="text-4xl block mx-auto mb-3"></iconify-icon>
                    <p>Belum ada paket. Isi form di sebelah kiri untuk menambahkan.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
// ─── Edit: isi form dari data paket ──────────────────────────────────────────
function fillEditForm(id, type, name, interval, sessions, multiplier, description, isActive) {
    const form = document.getElementById('packageForm');
    const baseUrl = '{{ url("subscription-packages") }}';

    form.action = `${baseUrl}/${id}`;
    document.getElementById('formMethod').value  = 'PUT';
    document.getElementById('formTitle').textContent = 'Edit Paket';
    document.getElementById('submitBtn').textContent = 'Update';
    document.getElementById('cancelEditBtn').style.display = 'inline-flex';

    // Isi field
    document.getElementById('fieldType').value        = type;
    document.getElementById('fieldType').disabled     = true; // tipe tidak bisa diubah
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

// ─── Reset form ke mode tambah ────────────────────────────────────────────────
function resetForm() {
    const form = document.getElementById('packageForm');
    form.action = '{{ route("subscription-packages.store") }}';
    form.reset();
    document.getElementById('formMethod').value  = 'POST';
    document.getElementById('formTitle').textContent = 'Tambah Paket';
    document.getElementById('submitBtn').textContent = 'Simpan';
    document.getElementById('cancelEditBtn').style.display = 'none';
    document.getElementById('fieldType').disabled = false;
    document.getElementById('discountPreview').textContent = '';
    document.getElementById('pricePreview').style.display = 'none';
    document.getElementById('typeNote').textContent = '';
}

// ─── Live preview diskon ──────────────────────────────────────────────────────
function updateDiscountPreview() {
    const multiplier = parseFloat(document.getElementById('fieldMultiplier').value);
    const sessions   = parseInt(document.getElementById('fieldSessions').value) || 0;
    const preview    = document.getElementById('discountPreview');
    const priceBox   = document.getElementById('pricePreview');

    if (!isNaN(multiplier) && multiplier > 0 && multiplier <= 1) {
        const pct = Math.round((1 - multiplier) * 100);
        preview.textContent = pct > 0 ? `hemat ${pct}%` : 'harga normal';

        if (sessions > 0) {
            const normal = 100000 * sessions;
            const final  = Math.round(normal * multiplier);
            document.getElementById('previewNormal').textContent =
                'Normal: Rp ' + normal.toLocaleString('id-ID');
            document.getElementById('previewFinal').textContent =
                'Rp ' + final.toLocaleString('id-ID') + '/tahun';
            priceBox.style.display = 'block';
        }
    } else {
        preview.textContent = '';
        priceBox.style.display = 'none';
    }
}

// Update preview saat sessions berubah juga
document.getElementById('fieldSessions').addEventListener('input', updateDiscountPreview);
</script>
@endpush

@endsection