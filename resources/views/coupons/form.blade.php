@extends('layouts.app')
@section('title', $coupon ? 'Edit Kupon' : 'Buat Kupon')
@section('page-title', $coupon ? 'Edit Kupon' : 'Buat Kupon Baru')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li><a href="{{ route('coupons.index') }}" class="dark:text-white hover:text-primary-600">Kupon</a></li>
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">{{ $coupon ? 'Edit' : 'Buat Baru' }}</li>
@endsection

@section('content')
<div class="flex flex-wrap gap-3 mb-6">
    <a href="{{ route('coupons.index') }}" class="btn btn-neutral-200 flex items-center gap-2">
        <iconify-icon icon="lucide:arrow-left"></iconify-icon> Kembali
    </a>
</div>

<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12 lg:col-span-8">
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-4 px-6">
                <h6 class="font-semibold">{{ $coupon ? 'Edit Kupon' : 'Buat Kupon Baru' }}</h6>
            </div>
            <div class="card-body p-6">

                @if($errors->any())
                    <div class="bg-danger-50 text-danger-600 px-4 py-3 rounded mb-4">
                        <ul class="list-disc list-inside text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ $coupon ? route('coupons.update', $coupon) : route('coupons.store') }}"
                      method="POST">
                    @csrf
                    @if($coupon) @method('PUT') @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        {{-- Kode Kupon --}}
                        <div>
                            <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                Kode Kupon <span class="text-danger-600">*</span>
                            </label>
                            <div class="flex gap-2">
                                <input type="text" name="code" id="code"
                                    value="{{ old('code', $coupon?->code) }}"
                                    class="form-control radius-8 font-mono uppercase"
                                    placeholder="DIKARI10" required>
                                <button type="button" onclick="generateCode()"
                                    class="btn btn-neutral-200 whitespace-nowrap flex items-center gap-1 text-sm">
                                    <iconify-icon icon="lucide:shuffle"></iconify-icon>
                                    Random
                                </button>
                            </div>
                        </div>

                        {{-- Nama Kupon --}}
                        <div>
                            <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                Nama Kupon <span class="text-danger-600">*</span>
                            </label>
                            <input type="text" name="name"
                                value="{{ old('name', $coupon?->name) }}"
                                class="form-control radius-8"
                                placeholder="Diskon Lebaran 10%" required>
                        </div>

                        {{-- Diskon % --}}
                        <div>
                            <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                Diskon (%) <span class="text-danger-600">*</span>
                            </label>
                            <input type="number" name="discount_percent" min="1" max="100" step="0.01"
                                value="{{ old('discount_percent', $coupon?->discount_percent) }}"
                                class="form-control radius-8"
                                placeholder="10" required>
                        </div>

                        {{-- Maks Diskon --}}
                        <div>
                            <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                Maksimal Diskon (Rp)
                            </label>
                            <input type="number" name="max_discount" min="0"
                                value="{{ old('max_discount', $coupon?->max_discount) }}"
                                class="form-control radius-8"
                                placeholder="Kosongkan jika tidak ada batas">
                        </div>

                        {{-- Min Order --}}
                        <div>
                            <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                Minimal Order (Rp) <span class="text-danger-600">*</span>
                            </label>
                            <input type="number" name="min_order" min="0"
                                value="{{ old('min_order', $coupon?->min_order) }}"
                                class="form-control radius-8"
                                placeholder="0" required>
                        </div>

                        {{-- Maks Pemakaian per User --}}
                        <div>
                            <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                Maks Pemakaian per User <span class="text-danger-600">*</span>
                            </label>
                            <input type="number" name="max_usage_per_user" min="1"
                                value="{{ old('max_usage_per_user', $coupon?->max_usage_per_user ?? 1) }}"
                                class="form-control radius-8"
                                required>
                        </div>

                        {{-- Valid From --}}
                        <div>
                            <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                Berlaku Dari <span class="text-danger-600">*</span>
                            </label>
                            <input type="date" name="valid_from"
                                value="{{ old('valid_from', $coupon?->valid_from?->format('Y-m-d')) }}"
                                class="form-control radius-8" required>
                        </div>

                        {{-- Valid Until --}}
                        <div>
                            <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                Berlaku Sampai <span class="text-danger-600">*</span>
                            </label>
                            <input type="date" name="valid_until"
                                value="{{ old('valid_until', $coupon?->valid_until?->format('Y-m-d')) }}"
                                class="form-control radius-8" required>
                        </div>

                    </div>

                    <div class="flex items-center gap-6 mt-4">
                        {{-- All Services --}}
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="all_services" value="0">
                            <input type="checkbox" name="all_services" value="1" id="all_services"
                                class="w-4 h-4"
                                {{ old('all_services', $coupon?->all_services) ? 'checked' : '' }}>
                            <label for="all_services" class="text-sm font-medium cursor-pointer">
                                Berlaku untuk semua layanan
                            </label>
                        </div>

                        {{-- Is Active --}}
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" id="is_active"
                                class="w-4 h-4"
                                {{ old('is_active', $coupon ? $coupon->is_active : true) ? 'checked' : '' }}>
                            <label for="is_active" class="text-sm font-medium cursor-pointer">
                                Aktifkan kupon
                            </label>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="submit" class="btn btn-primary-600 flex items-center gap-2">
                            <iconify-icon icon="lucide:save"></iconify-icon>
                            {{ $coupon ? 'Simpan Perubahan' : 'Buat Kupon' }}
                        </button>
                        <a href="{{ route('coupons.index') }}" class="btn btn-neutral-200">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Preview --}}
    <div class="col-span-12 lg:col-span-4">
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-4 px-6">
                <h6 class="font-semibold text-sm">Preview Kupon</h6>
            </div>
            <div class="card-body p-6">
                <div class="bg-gradient-to-r from-primary-600 to-primary-400 rounded-xl p-5 text-white">
                    <p class="text-xs opacity-80 mb-1">KODE KUPON</p>
                    <p class="text-2xl font-bold font-mono mb-3" id="preview-code">-</p>
                    <p class="text-sm opacity-90" id="preview-name">Nama kupon</p>
                    <div class="border-t border-white/20 mt-3 pt-3">
                        <p class="text-xl font-bold" id="preview-discount">0%</p>
                        <p class="text-xs opacity-80">diskon</p>
                    </div>
                </div>
                <div class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-secondary-light">Maks diskon</span>
                        <span id="preview-max" class="font-medium">Tidak ada batas</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-secondary-light">Min order</span>
                        <span id="preview-min" class="font-medium">-</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
async function generateCode() {
    const res = await fetch('{{ route('coupons.generate') }}');
    const data = await res.json();
    document.getElementById('code').value = data.code;
    updatePreview();
}

function updatePreview() {
    document.getElementById('preview-code').textContent =
        document.querySelector('[name="code"]').value || '-';
    document.getElementById('preview-name').textContent =
        document.querySelector('[name="name"]').value || 'Nama kupon';
    document.getElementById('preview-discount').textContent =
        (document.querySelector('[name="discount_percent"]').value || '0') + '%';

    const max = document.querySelector('[name="max_discount"]').value;
    document.getElementById('preview-max').textContent = max
        ? 'Rp ' + parseInt(max).toLocaleString('id-ID') : 'Tidak ada batas';

    const min = document.querySelector('[name="min_order"]').value;
    document.getElementById('preview-min').textContent = min
        ? 'Rp ' + parseInt(min).toLocaleString('id-ID') : '-';
}

document.querySelectorAll('input').forEach(el => {
    el.addEventListener('input', updatePreview);
});

updatePreview();
</script>
@endpush
@endsection