@extends('layouts.app')
@section('title', 'Tambah BP Service')
@section('page-title', 'Tambah BP Service')

@section('breadcrumb')
    <li><a href="{{ route('bp-services.index') }}" class="dark:text-white">BP Services</a></li>
    <li class="font-medium dark:text-white">Tambah</li>
@endsection

@section('content')
<div class="card border-0 mt-6">
    <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6">
        <h6 class="text-lg font-semibold mb-0">Form Tambah BP Service</h6>
    </div>
    <div class="card-body p-6">

        @if($errors->any())
            <div class="bg-danger-100 text-danger-600 px-4 py-3 rounded mb-4">
                <ul class="mb-0 list-disc pl-4">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('bp-services.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                @if(auth()->user()->role === 'adminsuper')
                <div>
                    <label class="form-label font-medium text-sm">Business Partner <span class="text-danger-600">*</span></label>
                    <select name="bp_id" class="form-control">
                        <option value="">-- Pilih BP --</option>
                        @foreach($businessPartners as $bp)
                            <option value="{{ $bp->id }}" {{ old('bp_id') == $bp->id ? 'selected' : '' }}>
                                {{ $bp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label class="form-label font-medium text-sm">Jenis Layanan <span class="text-danger-600">*</span></label>
                    <select name="service_type_id" class="form-control">
                        <option value="">-- Pilih Jenis Layanan --</option>
                        @foreach($serviceTypes as $type)
                            <option value="{{ $type->id }}" {{ old('service_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label font-medium text-sm">Harga Dasar <span class="text-danger-600">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="base_service" value="{{ old('base_service') }}"
                               class="form-control" placeholder="0">
                    </div>
                </div>

                <div>
                    <label class="form-label font-medium text-sm">Diskon (%)</label>
                    <div class="input-group">
                        <input type="number" name="discount" value="{{ old('discount', 0) }}"
                               class="form-control" placeholder="0" min="0" max="100">
                        <span class="input-group-text">%</span>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="form-label font-medium text-sm">Banner</label>
                    <input type="file" name="banner" id="banner" accept="image/*"
                           class="form-control" onchange="previewBanner(event)">
                    <p class="text-xs text-secondary-light mt-1">Format: JPG, JPEG, PNG, WEBP. Maks 2MB.</p>
                    <div id="banner-preview" class="mt-3 hidden">
                        <img id="preview-img" src="" alt="Preview" class="h-40 rounded-lg object-cover">
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <label class="form-label font-medium text-sm mb-0">Status Aktif</label>
                    <input type="checkbox" name="is_active" value="1"
                           class="w-5 h-5" {{ old('is_active') ? 'checked' : '' }}>
                </div>

            </div>

            <div class="flex items-center gap-3 mt-6">
                <button type="submit" class="btn btn-primary-600">Simpan</button>
                <a href="{{ route('bp-services.index') }}" class="btn btn-neutral-200">Batal</a>
            </div>
        </form>

    </div>
</div>
@endsection

@push('scripts')
<script>
function previewBanner(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('banner-preview').classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
}
</script>
@endpush