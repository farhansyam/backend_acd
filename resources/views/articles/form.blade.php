@extends('layouts.app')
@section('title', $article ? 'Edit Artikel' : 'Tambah Artikel')
@section('page-title', $article ? 'Edit Artikel' : 'Tambah Artikel')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li><a href="{{ route('articles.index') }}" class="dark:text-white hover:text-primary-600">Promo & Tips</a></li>
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">{{ $article ? 'Edit' : 'Tambah' }}</li>
@endsection

@section('content')
<div class="flex gap-3 mb-6">
    <a href="{{ route('articles.index') }}" class="btn btn-neutral-200 flex items-center gap-2">
        <iconify-icon icon="lucide:arrow-left"></iconify-icon> Kembali
    </a>
</div>

<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12 lg:col-span-8">
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-4 px-6">
                <h6 class="font-semibold">{{ $article ? 'Edit Artikel' : 'Tambah Artikel Baru' }}</h6>
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

                <form action="{{ $article ? route('articles.update', $article) : route('articles.store') }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf
                    @if($article) @method('PUT') @endif

                    <div class="space-y-4">

                        {{-- Tipe --}}
                        <div>
                            <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                Tipe <span class="text-danger-600">*</span>
                            </label>
                            <div class="flex gap-3">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="type" value="promo"
                                        {{ old('type', $article?->type ?? 'tips') === 'promo' ? 'checked' : '' }}>
                                    <span class="text-sm font-medium">🏷️ Promo</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="type" value="tips"
                                        {{ old('type', $article?->type ?? 'tips') === 'tips' ? 'checked' : '' }}>
                                    <span class="text-sm font-medium">💡 Tips</span>
                                </label>
                            </div>
                        </div>

                        {{-- Judul --}}
                        <div>
                            <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                Judul <span class="text-danger-600">*</span>
                            </label>
                            <input type="text" name="title"
                                value="{{ old('title', $article?->title) }}"
                                class="form-control radius-8"
                                placeholder="Contoh: Promo Cuci AC Hemat 20%" required>
                        </div>

                        {{-- Subtitle --}}
                        <div>
                            <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                Subtitle
                            </label>
                            <input type="text" name="subtitle"
                                value="{{ old('subtitle', $article?->subtitle) }}"
                                class="form-control radius-8"
                                placeholder="Contoh: Gunakan kode: DINGINHEMAT">
                        </div>

                        {{-- Warna --}}
                        <div>
                            <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                Warna Background <span class="text-danger-600">*</span>
                            </label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="color_hex" id="colorPicker"
                                    value="{{ old('color_hex', $article?->color_hex ?? '#1976D2') }}"
                                    class="w-12 h-10 rounded cursor-pointer border border-neutral-200"
                                    onchange="document.getElementById('colorHex').value = this.value">
                                <input type="text" id="colorHex"
                                    value="{{ old('color_hex', $article?->color_hex ?? '#1976D2') }}"
                                    class="form-control radius-8 font-mono w-32"
                                    onchange="document.getElementById('colorPicker').value = this.value"
                                    oninput="document.getElementById('colorPicker').value = this.value; this.form.querySelector('[name=color_hex]').value = this.value">
                                {{-- Hidden input untuk submit --}}
                                <input type="hidden" name="color_hex" id="colorHexHidden"
                                    value="{{ old('color_hex', $article?->color_hex ?? '#1976D2') }}">
                            </div>
                            <p class="text-xs text-secondary-light mt-1">Pilih warna untuk kartu di beranda</p>
                        </div>

                        {{-- Gambar --}}
                        <div>
                            <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                Gambar (Opsional)
                            </label>
                            @if($article?->image)
                                <div class="mb-2">
                                    <img src="{{ $article->image_url }}" class="h-24 rounded-lg object-cover">
                                </div>
                            @endif
                            <input type="file" name="image" accept="image/*" class="form-control radius-8">
                            <p class="text-xs text-secondary-light mt-1">Maks 2MB. Format: JPG, PNG, WebP</p>
                        </div>

                        {{-- Kadaluarsa --}}
                        <div>
                            <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                Berlaku Sampai
                            </label>
                            <input type="date" name="expired_at"
                                value="{{ old('expired_at', $article?->expired_at?->format('Y-m-d')) }}"
                                class="form-control radius-8">
                            <p class="text-xs text-secondary-light mt-1">Kosongkan jika tidak ada batas waktu</p>
                        </div>

                        {{-- Konten --}}
                        <div>
                            <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                                Konten / Deskripsi
                            </label>
                            <textarea name="content" rows="5" class="form-control radius-8"
                                placeholder="Isi artikel atau detail promo...">{{ old('content', $article?->content) }}</textarea>
                        </div>

                        {{-- Status --}}
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" id="is_active"
                                class="w-4 h-4"
                                {{ old('is_active', $article ? $article->is_active : true) ? 'checked' : '' }}>
                            <label for="is_active" class="text-sm font-medium cursor-pointer">
                                Tampilkan di aplikasi
                            </label>
                        </div>

                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="submit" class="btn btn-primary-600 flex items-center gap-2">
                            <iconify-icon icon="lucide:save"></iconify-icon>
                            {{ $article ? 'Simpan Perubahan' : 'Tambah Artikel' }}
                        </button>
                        <a href="{{ route('articles.index') }}" class="btn btn-neutral-200">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Preview --}}
    <div class="col-span-12 lg:col-span-4">
        <div class="card border-0 sticky top-4">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-4 px-6">
                <h6 class="font-semibold text-sm">Preview Kartu</h6>
            </div>
            <div class="card-body p-6">
                <div id="preview-card" class="rounded-2xl p-6 text-white"
                     style="background: linear-gradient(135deg, {{ $article?->color_hex ?? '#1976D2' }}, {{ $article?->color_hex ?? '#1976D2' }}bb)">
                    <p class="text-xs opacity-80 mb-2 font-bold tracking-widest" id="preview-type">TIPS</p>
                    <p class="text-lg font-black leading-tight mb-1" id="preview-title">Judul artikel</p>
                    <p class="text-sm opacity-85" id="preview-subtitle">Subtitle artikel</p>
                </div>
                <p class="text-xs text-secondary-light mt-3 text-center">Preview tampilan di beranda app</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Sync color picker
document.getElementById('colorPicker').addEventListener('input', function() {
    const val = this.value;
    document.getElementById('colorHex').value = val;
    document.getElementById('colorHexHidden').value = val;
    document.getElementById('preview-card').style.background =
        `linear-gradient(135deg, ${val}, ${val}bb)`;
});

// Sync preview
document.querySelector('[name="title"]').addEventListener('input', function() {
    document.getElementById('preview-title').textContent = this.value || 'Judul artikel';
});
document.querySelector('[name="subtitle"]').addEventListener('input', function() {
    document.getElementById('preview-subtitle').textContent = this.value || 'Subtitle artikel';
});
document.querySelectorAll('[name="type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('preview-type').textContent =
            this.value === 'promo' ? '🏷️ PROMO' : '💡 TIPS';
    });
});
</script>
@endpush
@endsection