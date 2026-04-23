@extends('layouts.app')
@section('title', 'Edit Jenis Layanan')
@section('page-title', 'Edit Jenis Layanan')

@section('breadcrumb')
    <li><a href="{{ route('service-types.index') }}" class="dark:text-white">Jenis Layanan</a></li>
    <li class="font-medium dark:text-white">Edit</li>
@endsection

@section('content')
<div class="card border-0 mt-6">
    <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6">
        <h6 class="text-lg font-semibold mb-0">Form Edit Jenis Layanan</h6>
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

        <form action="{{ route('service-types.update', $serviceType) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                {{-- Kategori --}}
                <div class="md:col-span-2">
                    <label class="form-label font-medium text-sm">Kategori <span class="text-danger-600">*</span></label>
                    <select name="category" class="form-control radius-8" required>
                        <option value="cuci_reguler"     {{ old('category', $serviceType->category) === 'cuci_reguler'     ? 'selected' : '' }}>Cuci Reguler</option>
                        <option value="pasang_baru"      {{ old('category', $serviceType->category) === 'pasang_baru'      ? 'selected' : '' }}>Pasang Baru</option>
                        <option value="unit"             {{ old('category', $serviceType->category) === 'unit'             ? 'selected' : '' }}>Unit AC</option>
                        <option value="relokasi"         {{ old('category', $serviceType->category) === 'relokasi'         ? 'selected' : '' }}>Relokasi (1 Lokasi)</option>
                        <option value="relokasi_bongkar" {{ old('category', $serviceType->category) === 'relokasi_bongkar' ? 'selected' : '' }}>Relokasi Bongkar</option>
                        <option value="relokasi_pasang"  {{ old('category', $serviceType->category) === 'relokasi_pasang'  ? 'selected' : '' }}>Relokasi Pasang</option>
                        <option value="service_perbaikan_survey"  {{ old('category', $serviceType->category ?? '') === 'service_perbaikan_survey'  ? 'selected' : '' }}>Perbaikan — Biaya Survey</option>
                <option value="service_perbaikan_service" {{ old('category', $serviceType->category ?? '') === 'service_perbaikan_service' ? 'selected' : '' }}>Perbaikan — Biaya Service</option>
                    </select>
                </div>

                {{-- Nama --}}
                <div class="md:col-span-2">
                    <label class="form-label font-medium text-sm">Nama Layanan <span class="text-danger-600">*</span></label>
                    <input type="text" name="name"
                           value="{{ old('name', $serviceType->name) }}"
                           class="form-control radius-8"
                           placeholder="Contoh: Relokasi Bongkar AC Split" required>
                </div>

                {{-- Deskripsi --}}
                <div class="md:col-span-2">
                    <label class="form-label font-medium text-sm">Deskripsi</label>
                    <textarea name="description" rows="4"
                              class="form-control radius-8"
                              placeholder="Deskripsi jenis layanan...">{{ old('description', $serviceType->description) }}</textarea>
                </div>

                {{-- Status --}}
                <div>
                    <label class="form-label font-medium text-sm">Status <span class="text-danger-600">*</span></label>
                    <select name="is_active" class="form-control radius-8" required>
                        <option value="1" {{ old('is_active', $serviceType->is_active) == '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('is_active', $serviceType->is_active) == '0' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

            </div>

            <div class="flex items-center gap-3 mt-6">
                <button type="submit" class="btn btn-primary-600 flex items-center gap-2">
                    <iconify-icon icon="lucide:save"></iconify-icon>
                    Simpan Perubahan
                </button>
                <a href="{{ route('service-types.index') }}" class="btn btn-neutral-200">Batal</a>
            </div>
        </form>

    </div>
</div>
@endsection