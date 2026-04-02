@extends('layouts.app')
@section('title', 'Tambah Business Partner')
@section('page-title', 'Tambah Business Partner')

@section('breadcrumb')
    <li><a href="{{ route('business-partners.index') }}" class="dark:text-white">Business Partner</a></li>
    <li class="font-medium dark:text-white">Tambah</li>
@endsection

@section('content')
<div class="card border-0 mt-6">
    <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6">
        <h6 class="text-lg font-semibold mb-0">Form Tambah Business Partner</h6>
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

        <form action="{{ route('business-partners.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <label class="form-label font-medium text-sm">Nama <span class="text-danger-600">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="form-control" placeholder="Nama Business Partner">
                </div>

                <div>
                    <label class="form-label font-medium text-sm">Email <span class="text-danger-600">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="form-control" placeholder="email@example.com">
                </div>

                <div>
                    <label class="form-label font-medium text-sm">Password <span class="text-danger-600">*</span></label>
                    <input type="password" name="password"
                           class="form-control" placeholder="Min. 8 karakter">
                </div>

                <div>
                    <label class="form-label font-medium text-sm">Konfirmasi Password <span class="text-danger-600">*</span></label>
                    <input type="password" name="password_confirmation"
                           class="form-control" placeholder="Ulangi password">
                </div>

                <div>
                    <label class="form-label font-medium text-sm">Provinsi</label>
                    <select name="province" id="province" class="form-control">
                        <option value="">-- Pilih Provinsi --</option>
                    </select>
                </div>

                <div>
                    <label class="form-label font-medium text-sm">Kota / Kabupaten</label>
                    <select name="city" id="city" class="form-control">
                        <option value="">-- Pilih Provinsi dulu --</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="form-label font-medium text-sm">Alamat</label>
                    <textarea name="address" rows="3"
                              class="form-control" placeholder="Alamat lengkap">{{ old('address') }}</textarea>
                </div>

            </div>

            <div class="flex items-center gap-3 mt-6">
                <button type="submit" class="btn btn-primary-600">Simpan</button>
                <a href="{{ route('business-partners.index') }}" class="btn btn-neutral-200">Batal</a>
            </div>
        </form>

    </div>
</div>
@endsection

@push('scripts')
<script>
const provinceSelect = document.getElementById('province');
const citySelect = document.getElementById('city');

// Load provinsi
fetch('/api/provinces')
    .then(res => res.json())
    .then(data => {
        data.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.name;
            opt.dataset.id = p.id;
            opt.text = p.name;
            @if(old('province'))
            if (p.name === "{{ old('province') }}") opt.selected = true;
            @endif
            provinceSelect.appendChild(opt);
        });

        // Kalau ada old value, langsung load kota
        @if(old('province'))
        provinceSelect.dispatchEvent(new Event('change'));
        @endif
    });

// Load kota saat provinsi dipilih
provinceSelect.addEventListener('change', function () {
    const selectedOption = this.options[this.selectedIndex];
    const provinceId = selectedOption.dataset.id;

    citySelect.innerHTML = '<option value="">-- Memuat kota... --</option>';

    if (!provinceId) {
        citySelect.innerHTML = '<option value="">-- Pilih Provinsi dulu --</option>';
        return;
    }
    
fetch(`/api/regencies/${provinceId}`)
        .then(res => res.json())
        .then(data => {
            citySelect.innerHTML = '<option value="">-- Pilih Kota --</option>';
            data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.name;
                opt.text = c.name;
                @if(old('city'))
                if (c.name === "{{ old('city') }}") opt.selected = true;
                @endif
                citySelect.appendChild(opt);
            });
        });
});
</script>
@endpush