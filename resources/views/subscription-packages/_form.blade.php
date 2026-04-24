{{-- Dipakai di modal create dan edit --}}
{{-- $package = null untuk create, SubscriptionPackage object untuk edit --}}

<div>
    <label class="form-label fw-semibold text-primary-light text-sm mb-2">
        Nama Paket <span class="text-danger-600">*</span>
    </label>
    <input type="text" name="name" class="form-control radius-8"
        value="{{ old('name', $package?->name) }}"
        placeholder="Contoh: Paket Hemat" required>
</div>

<div>
    <label class="form-label fw-semibold text-primary-light text-sm mb-2">
        Tipe <span class="text-danger-600">*</span>
    </label>
    @if($package)
        {{-- Tipe tidak bisa diubah karena dipakai sebagai identifier --}}
        <input type="hidden" name="type" value="{{ $package->type }}">
        <div class="form-control radius-8 bg-neutral-50 text-secondary-light">
            {{ ucfirst($package->type) }} <span class="text-xs">(tidak bisa diubah)</span>
        </div>
    @else
        <select name="type" class="form-control radius-8" required>
            <option value="">-- Pilih tipe --</option>
            <option value="hemat"    {{ old('type') === 'hemat'    ? 'selected' : '' }}>Hemat</option>
            <option value="rutin"    {{ old('type') === 'rutin'    ? 'selected' : '' }}>Rutin</option>
            <option value="intensif" {{ old('type') === 'intensif' ? 'selected' : '' }}>Intensif</option>
        </select>
    @endif
</div>

<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="form-label fw-semibold text-primary-light text-sm mb-2">
            Interval (bulan) <span class="text-danger-600">*</span>
        </label>
        <input type="number" name="interval_months" class="form-control radius-8"
            value="{{ old('interval_months', $package?->interval_months) }}"
            min="1" max="12" placeholder="Contoh: 6" required>
        <p class="text-xs text-secondary-light mt-1">Jarak antar sesi</p>
    </div>
    <div>
        <label class="form-label fw-semibold text-primary-light text-sm mb-2">
            Total Sesi <span class="text-danger-600">*</span>
        </label>
        <input type="number" name="total_sessions" class="form-control radius-8"
            value="{{ old('total_sessions', $package?->total_sessions) }}"
            min="1" max="24" placeholder="Contoh: 2" required>
        <p class="text-xs text-secondary-light mt-1">Jumlah cuci per tahun</p>
    </div>
</div>

<div>
    <label class="form-label fw-semibold text-primary-light text-sm mb-2">
        Price Multiplier <span class="text-danger-600">*</span>
    </label>
    <input type="number" name="price_multiplier" class="form-control radius-8"
        value="{{ old('price_multiplier', $package?->price_multiplier) }}"
        min="0.1" max="1" step="0.01" placeholder="Contoh: 0.90" required
        id="multiplierInput{{ $package?->id ?? 'new' }}"
        oninput="updateDiscount(this)">
    <p class="text-xs text-secondary-light mt-1">
        Angka antara 0.1–1.0 · 1.0 = harga normal ·
        <span id="discountLabel{{ $package?->id ?? 'new' }}" class="text-success-600 font-medium">
            @if($package)
                diskon {{ round((1 - $package->price_multiplier) * 100) }}%
            @endif
        </span>
    </p>
</div>

<div>
    <label class="form-label fw-semibold text-primary-light text-sm mb-2">Deskripsi</label>
    <textarea name="description" class="form-control radius-8" rows="2"
        placeholder="Deskripsi singkat paket...">{{ old('description', $package?->description) }}</textarea>
</div>

<div>
    <label class="form-label fw-semibold text-primary-light text-sm mb-2">
        Status <span class="text-danger-600">*</span>
    </label>
    <select name="is_active" class="form-control radius-8" required>
        <option value="1" {{ old('is_active', $package?->is_active ?? 1) == 1 ? 'selected' : '' }}>Aktif</option>
        <option value="0" {{ old('is_active', $package?->is_active ?? 1) == 0 ? 'selected' : '' }}>Nonaktif</option>
    </select>
</div>

<script>
function updateDiscount(input) {
    const id = input.id.replace('multiplierInput', '');
    const label = document.getElementById('discountLabel' + id);
    if (!label) return;
    const val = parseFloat(input.value);
    if (!isNaN(val) && val > 0 && val <= 1) {
        const pct = Math.round((1 - val) * 100);
        label.textContent = pct > 0 ? `diskon ${pct}%` : 'harga normal (tidak ada diskon)';
    } else {
        label.textContent = '';
    }
}
</script>
