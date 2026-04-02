<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar sebagai Teknisi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen py-10">
<div class="max-w-2xl mx-auto px-4">

    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Daftar sebagai Teknisi</h1>
        <p class="text-gray-500 mt-1">Isi form di bawah ini untuk mendaftar. Pendaftaran akan diverifikasi oleh Business Partner di area kamu.</p>
    </div>

    <div class="bg-white rounded-2xl shadow p-8">

        @if($errors->any())
            <div class="bg-red-50 text-red-600 px-4 py-3 rounded-lg mb-6">
                <ul class="list-disc pl-4 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('technician.register.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="space-y-5">

                <p class="font-semibold text-gray-700 border-b pb-2">Data Akun</p>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Nama lengkap sesuai KTP">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="email@example.com">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Min. 8 karakter">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. HP <span class="text-red-500">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="08xxxxxxxxxx">
                </div>

                <p class="font-semibold text-gray-700 border-b pb-2 pt-2">Wilayah</p>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Provinsi <span class="text-red-500">*</span></label>
                        <select name="province" id="province"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Provinsi --</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kota / Kabupaten <span class="text-red-500">*</span></label>
                        <select name="city" id="city"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Provinsi dulu --</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
                    <textarea name="address" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Alamat lengkap">{{ old('address') }}</textarea>
                </div>

                <p class="font-semibold text-gray-700 border-b pb-2 pt-2">Dokumen</p>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Foto KTP <span class="text-red-500">*</span></label>
                    <input type="file" name="ktp_photo" accept="image/*"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm">
                    <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG. Maks 2MB.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Foto Selfie <span class="text-red-500">*</span></label>
                    <input type="file" name="selfie_photo" accept="image/*"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm">
                    <p class="text-xs text-gray-400 mt-1">Foto selfie memegang KTP. Format: JPG, PNG. Maks 2MB.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">File SKCK</label>
                    <input type="file" name="skck_file" accept=".pdf,image/*"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm">
                    <p class="text-xs text-gray-400 mt-1">Format: PDF, JPG, PNG. Maks 2MB.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sertifikat Keahlian</label>
                    <input type="file" name="certificate" accept=".pdf,image/*"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm">
                    <p class="text-xs text-gray-400 mt-1">Format: PDF, JPG, PNG. Maks 2MB.</p>
                </div>

            </div>

            <button type="submit"
                    class="w-full mt-8 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition">
                Kirim Pendaftaran
            </button>

        </form>
    </div>
</div>

<script>
const provinceSelect = document.getElementById('province');
const citySelect = document.getElementById('city');

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
        @if(old('province'))
        provinceSelect.dispatchEvent(new Event('change'));
        @endif
    });

provinceSelect.addEventListener('change', function () {
    const id = this.options[this.selectedIndex].dataset.id;
    citySelect.innerHTML = '<option value="">-- Memuat kota... --</option>';
    if (!id) { citySelect.innerHTML = '<option value="">-- Pilih Provinsi dulu --</option>'; return; }
    fetch(`/api/regencies/${id}`)
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
</body>
</html>