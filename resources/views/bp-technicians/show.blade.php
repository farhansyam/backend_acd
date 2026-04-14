@extends('layouts.app')
@section('title', 'Detail Teknisi — ' . $technician->user->name)
@section('page-title', 'Detail Teknisi')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">
        <a href="{{ route('bp-technicians.index') }}" class="hover:text-primary-600">Teknisi Lokal</a>
    </li>
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">{{ $technician->user->name }}</li>
@endsection

@section('content')

@php
    $statusColor = match($technician->status) {
        'approved' => 'bg-success-100 text-success-600',
        'rejected' => 'bg-danger-100 text-danger-600',
        default    => 'bg-warning-100 text-warning-600',
    };
    $statusLabel = match($technician->status) {
        'approved' => 'Aktif',
        'rejected' => 'Ditolak',
        default    => 'Menunggu Approval',
    };
    $gradeColor = match($technician->grade) {
        'beginner' => 'bg-info-100 text-info-600',
        'medium'   => 'bg-warning-100 text-warning-600',
        'pro'      => 'bg-success-100 text-success-600',
        default    => 'bg-neutral-100 text-neutral-500',
    };
@endphp

<div class="flex flex-wrap gap-4 mb-6">
    <a href="{{ route('bp-technicians.index') }}"
       class="btn btn-neutral-200 flex items-center gap-2">
        <iconify-icon icon="lucide:arrow-left"></iconify-icon>
        Kembali
    </a>

    {{-- Tombol approve/reject hanya kalau masih pending --}}
    @if($technician->isPending())
        <button onclick="openApproveModal({{ $technician->id }})"
                class="btn btn-success-600 flex items-center gap-2">
            <iconify-icon icon="lucide:check"></iconify-icon>
            Approve
        </button>
        <button onclick="openRejectModal({{ $technician->id }})"
                class="btn btn-danger-600 flex items-center gap-2">
            <iconify-icon icon="lucide:x"></iconify-icon>
            Reject
        </button>
    @endif

    {{-- Tombol nonaktifkan kalau sudah approved --}}
    @if($technician->isApproved())
        <form action="{{ route('bp-technicians.destroy', $technician) }}" method="POST"
              onsubmit="return confirm('Yakin ingin menonaktifkan teknisi ini?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger-600 flex items-center gap-2">
                <iconify-icon icon="lucide:user-x"></iconify-icon>
                Nonaktifkan
            </button>
        </form>
    @endif
</div>

<div class="grid grid-cols-12 gap-6">

    {{-- ===== KOLOM KIRI — Profil & Status ===== --}}
    <div class="col-span-12 lg:col-span-4 flex flex-col gap-6">

        {{-- Kartu Profil --}}
        <div class="card border-0">
            <div class="card-body p-6 text-center">
                @if($technician->selfie_photo)
                    <a href="{{ asset('storage/' . $technician->selfie_photo) }}" target="_blank">
                        <img src="{{ asset('storage/' . $technician->selfie_photo) }}"
                             alt="Selfie {{ $technician->user->name }}"
                             class="w-20 h-20 rounded-full object-cover mx-auto mb-4 border-2 border-primary-200 shadow-sm">
                    </a>
                @else
                    <div class="w-20 h-20 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center text-3xl font-bold mx-auto mb-4">
                        {{ strtoupper(substr($technician->user->name, 0, 1)) }}
                    </div>
                @endif
                <h6 class="text-lg font-semibold mb-1">{{ $technician->user->name }}</h6>
                <p class="text-secondary-light text-sm mb-3">{{ $technician->user->email }}</p>

                <div class="flex flex-wrap gap-2 justify-center">
                    <span class="{{ $statusColor }} px-3 py-1 rounded-full text-xs font-medium flex items-center gap-1">
                        <iconify-icon icon="{{ $technician->isApproved() ? 'lucide:check-circle' : ($technician->isPending() ? 'lucide:clock' : 'lucide:x-circle') }}"></iconify-icon>
                        {{ $statusLabel }}
                    </span>
                    @if($technician->grade)
                        <span class="{{ $gradeColor }} px-3 py-1 rounded-full text-xs font-medium capitalize">
                            {{ $technician->grade }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Kartu Info Akun --}}
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-3 px-5">
                <h6 class="font-semibold text-sm mb-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:info" class="text-primary-600"></iconify-icon>
                    Info Akun
                </h6>
            </div>
            <div class="card-body p-5 space-y-3">
                <div class="flex items-center gap-3">
                    <iconify-icon icon="lucide:phone" class="text-secondary-light text-lg flex-shrink-0"></iconify-icon>
                    <div>
                        <p class="text-xs text-secondary-light">No. HP</p>
                        <p class="text-sm font-medium">{{ $technician->user->phone ?? '-' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <iconify-icon icon="lucide:calendar" class="text-secondary-light text-lg flex-shrink-0"></iconify-icon>
                    <div>
                        <p class="text-xs text-secondary-light">Tanggal Daftar</p>
                        <p class="text-sm font-medium">{{ $technician->created_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>
                @if($technician->approved_at)
                    <div class="flex items-center gap-3">
                        <iconify-icon icon="lucide:check-circle" class="text-success-500 text-lg flex-shrink-0"></iconify-icon>
                        <div>
                            <p class="text-xs text-secondary-light">Disetujui Pada</p>
                            <p class="text-sm font-medium">{{ $technician->approved_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                @endif
                @if($technician->isRejected() && $technician->rejection_reason)
                    <div class="bg-danger-50 dark:bg-danger-600/10 rounded-lg p-3 mt-2">
                        <p class="text-xs text-danger-600 font-medium mb-1 flex items-center gap-1">
                            <iconify-icon icon="lucide:alert-circle"></iconify-icon> Alasan Penolakan
                        </p>
                        <p class="text-sm text-danger-700 dark:text-danger-400">{{ $technician->rejection_reason }}</p>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ===== KOLOM KANAN — Detail Lengkap ===== --}}
    <div class="col-span-12 lg:col-span-8 flex flex-col gap-6">

        {{-- Wilayah --}}
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-3 px-5">
                <h6 class="font-semibold text-sm mb-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:map-pin" class="text-primary-600"></iconify-icon>
                    Wilayah Kerja
                </h6>
            </div>
            <div class="card-body p-5">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-xs text-secondary-light mb-1">Provinsi</p>
                        <p class="text-sm font-medium">{{ $technician->province ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-light mb-1">Kota / Kabupaten</p>
                        <p class="text-sm font-medium">{{ $technician->city ?? '-' }}</p>
                    </div>
                </div>

                {{-- Kecamatan --}}
                <div>
                    <p class="text-xs text-secondary-light mb-2">Kecamatan</p>
                    @if(!empty($technician->districts) && count($technician->districts) > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($technician->districts as $district)
                                <span class="bg-primary-50 dark:bg-primary-600/10 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-600/30 px-3 py-1 rounded-full text-xs font-medium">
                                    {{ $district }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-secondary-light italic">Belum ada kecamatan dipilih</p>
                    @endif
                </div>

                @if($technician->address)
                    <div class="mt-4 pt-4 border-t border-neutral-200 dark:border-neutral-600">
                        <p class="text-xs text-secondary-light mb-1">Alamat Lengkap</p>
                        <p class="text-sm">{{ $technician->address }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Dokumen --}}
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-3 px-5">
                <h6 class="font-semibold text-sm mb-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:file-text" class="text-primary-600"></iconify-icon>
                    Dokumen
                </h6>
            </div>
            <div class="card-body p-5">
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">

                    {{-- Foto KTP --}}
                    <div class="flex flex-col items-center gap-2">
                        @if($technician->ktp_photo)
                            <a href="{{ asset('storage/' . $technician->ktp_photo) }}" target="_blank"
                               class="w-full aspect-video rounded-lg overflow-hidden border border-neutral-200 dark:border-neutral-600 hover:border-primary-400 transition block">
                                <img src="{{ asset('storage/' . $technician->ktp_photo) }}"
                                     alt="KTP" class="w-full h-full object-cover">
                            </a>
                        @else
                            <div class="w-full aspect-video rounded-lg bg-neutral-100 dark:bg-neutral-700 flex items-center justify-center border border-dashed border-neutral-300 dark:border-neutral-600">
                                <iconify-icon icon="lucide:image-off" class="text-2xl text-neutral-400"></iconify-icon>
                            </div>
                        @endif
                        <span class="text-xs font-medium {{ $technician->ktp_photo ? 'text-primary-600' : 'text-neutral-400' }}">
                            Foto KTP
                        </span>
                    </div>

                    {{-- SKCK --}}
                    <div class="flex flex-col items-center gap-2">
                        @if($technician->skck_file)
                            @php $skckExt = pathinfo($technician->skck_file, PATHINFO_EXTENSION); @endphp
                            <a href="{{ asset('storage/' . $technician->skck_file) }}" target="_blank"
                               class="w-full aspect-video rounded-lg border border-warning-200 dark:border-warning-600/30 bg-warning-50 dark:bg-warning-600/10 flex flex-col items-center justify-center gap-1 hover:bg-warning-100 transition">
                                <iconify-icon icon="{{ $skckExt === 'pdf' ? 'lucide:file-text' : 'lucide:image' }}" class="text-2xl text-warning-600"></iconify-icon>
                                <span class="text-xs text-warning-600 uppercase font-bold">{{ $skckExt }}</span>
                            </a>
                        @else
                            <div class="w-full aspect-video rounded-lg bg-neutral-100 dark:bg-neutral-700 flex items-center justify-center border border-dashed border-neutral-300 dark:border-neutral-600">
                                <iconify-icon icon="lucide:file-x" class="text-2xl text-neutral-400"></iconify-icon>
                            </div>
                        @endif
                        <span class="text-xs font-medium {{ $technician->skck_file ? 'text-warning-600' : 'text-neutral-400' }}">
                            SKCK
                        </span>
                    </div>

                    {{-- Sertifikat --}}
                    <div class="flex flex-col items-center gap-2">
                        @if($technician->certificate)
                            @php $certExt = pathinfo($technician->certificate, PATHINFO_EXTENSION); @endphp
                            <a href="{{ asset('storage/' . $technician->certificate) }}" target="_blank"
                               class="w-full aspect-video rounded-lg border border-success-200 dark:border-success-600/30 bg-success-50 dark:bg-success-600/10 flex flex-col items-center justify-center gap-1 hover:bg-success-100 transition">
                                <iconify-icon icon="{{ $certExt === 'pdf' ? 'lucide:file-text' : 'lucide:image' }}" class="text-2xl text-success-600"></iconify-icon>
                                <span class="text-xs text-success-600 uppercase font-bold">{{ $certExt }}</span>
                            </a>
                        @else
                            <div class="w-full aspect-video rounded-lg bg-neutral-100 dark:bg-neutral-700 flex items-center justify-center border border-dashed border-neutral-300 dark:border-neutral-600">
                                <iconify-icon icon="lucide:file-x" class="text-2xl text-neutral-400"></iconify-icon>
                            </div>
                        @endif
                        <span class="text-xs font-medium {{ $technician->certificate ? 'text-success-600' : 'text-neutral-400' }}">
                            Sertifikat
                        </span>
                    </div>

                </div>
            </div>
        </div>
        {{-- Tambahkan ini di bp-technicians/show.blade.php --}}
{{-- Letakkan setelah section Dokumen (card border-0 terakhir di kolom kanan) --}}
{{-- Dan tambahkan section Aksi Performa di kolom kiri --}}

{{-- ===== TAMBAHAN DI KOLOM KIRI (setelah Kartu Info Akun) ===== --}}

{{-- Stats Performa --}}
<div class="card border-0">
    <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-3 px-5">
        <h6 class="font-semibold text-sm mb-0 flex items-center gap-2">
            <iconify-icon icon="lucide:bar-chart-2" class="text-primary-600"></iconify-icon>
            Statistik Performa
        </h6>
    </div>
    <div class="card-body p-5 space-y-3">
        <div class="flex justify-between items-center">
            <span class="text-sm text-secondary-light">Total Order Selesai</span>
            <span class="font-bold">{{ $totalCompleted }}</span>
        </div>
        <div class="flex justify-between items-center">
            <span class="text-sm text-secondary-light">Order Bulan Ini</span>
            <span class="font-bold text-primary-600">{{ $completedThisMonth }}</span>
        </div>
        <div class="flex justify-between items-center">
            <span class="text-sm text-secondary-light">Rating Rata-rata</span>
            <span class="font-bold text-warning-600">
                {{ $technician->avg_rating > 0 ? '⭐ ' . number_format($technician->avg_rating, 1) : '-' }}
            </span>
        </div>
        <div class="flex justify-between items-center">
            <span class="text-sm text-secondary-light">Total Pendapatan</span>
            <span class="font-bold">Rp {{ number_format($totalEarning, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between items-center">
            <span class="text-sm text-secondary-light">Saldo Sekarang</span>
            <span class="font-bold text-success-600">Rp {{ number_format($technician->balance, 0, ',', '.') }}</span>
        </div>
    </div>
</div>

{{-- Aksi Performa --}}
@if($technician->isApproved())
<div class="card border-0">
    <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-3 px-5">
        <h6 class="font-semibold text-sm mb-0 flex items-center gap-2">
            <iconify-icon icon="lucide:settings" class="text-primary-600"></iconify-icon>
            Aksi Performa
        </h6>
    </div>
    <div class="card-body p-5 space-y-3">

        {{-- Ubah Grade --}}
        <form action="{{ route('bp-technicians.update-grade', $technician) }}" method="POST">
            @csrf @method('PATCH')
            <label class="text-xs text-secondary-light mb-1 block">Ubah Grade</label>
            <div class="flex gap-2">
                <select name="grade" class="form-control radius-8 text-sm flex-1">
                    <option value="beginner" {{ $technician->grade === 'beginner' ? 'selected' : '' }}>Beginner (55%)</option>
                    <option value="medium"   {{ $technician->grade === 'medium'   ? 'selected' : '' }}>Medium (65%)</option>
                    <option value="pro"      {{ $technician->grade === 'pro'      ? 'selected' : '' }}>Pro (70%)</option>
                </select>
                <button type="submit" class="btn btn-primary-600 text-sm whitespace-nowrap">
                    Simpan
                </button>
            </div>
            <p class="text-xs text-secondary-light mt-1">Persentase pendapatan teknisi dari total order</p>
        </form>

        {{-- Suspend --}}
        <button onclick="openSuspendModal()" class="btn btn-danger-600 w-full flex items-center justify-center gap-2 mt-2">
            <iconify-icon icon="lucide:user-x"></iconify-icon>
            Suspend Teknisi
        </button>

    </div>
</div>
@endif

{{-- ===== TAMBAHAN DI KOLOM KANAN (setelah section Dokumen) ===== --}}

{{-- Ulasan Terbaru --}}
<div class="card border-0">
    <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-4 px-6">
        <h6 class="font-semibold mb-0 flex items-center gap-2">
            <iconify-icon icon="lucide:star" class="text-warning-500"></iconify-icon>
            Ulasan Terbaru
        </h6>
    </div>
    <div class="card-body p-0">
        @forelse($recentRatings as $rating)
        <div class="px-6 py-4 border-b border-neutral-100 dark:border-neutral-700 last:border-0">
            <div class="flex items-start justify-between mb-2">
                <div>
                    <p class="font-medium text-sm">{{ $rating->order?->user?->name ?? '-' }}</p>
                    <p class="text-xs text-secondary-light">Order #{{ $rating->order_id }}</p>
                </div>
                <div class="flex items-center gap-0.5">
                    @for($i = 1; $i <= 5; $i++)
                        <iconify-icon icon="lucide:star"
                            class="{{ $i <= $rating->rating ? 'text-warning-500' : 'text-neutral-300' }}"
                            style="font-size:14px"></iconify-icon>
                    @endfor
                    <span class="text-sm font-bold ml-1">{{ $rating->rating }}</span>
                </div>
            </div>
            @if($rating->review)
            <p class="text-sm text-secondary-light italic">"{{ $rating->review }}"</p>
            @endif
            <p class="text-xs text-secondary-light mt-1">{{ $rating->created_at->format('d M Y') }}</p>
        </div>
        @empty
        <div class="px-6 py-8 text-center text-secondary-light text-sm">Belum ada ulasan.</div>
        @endforelse
    </div>
</div>

{{-- Order Terbaru --}}
<div class="card border-0">
    <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-4 px-6">
        <h6 class="font-semibold mb-0">Order Terbaru</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table bordered-table style-two mb-0">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                    @php
                        $sc = match($order->status) {
                            'completed'  => 'bg-success-100 text-success-600',
                            'cancelled'  => 'bg-danger-100 text-danger-600',
                            'in_progress' => 'bg-purple-100 text-purple-600',
                            'warranty'   => 'bg-success-100 text-success-600',
                            'complained' => 'bg-danger-100 text-danger-600',
                            default      => 'bg-neutral-100 text-neutral-500',
                        };
                    @endphp
                    <tr>
                        <td class="font-medium">#{{ $order->id }}</td>
                        <td>{{ $order->user->name ?? '-' }}</td>
                        <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        <td>
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $sc }}">
                                {{ $order->status }}
                            </span>
                        </td>
                        <td class="text-sm text-secondary-light">
                            {{ $order->created_at->format('d M Y') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-secondary-light py-4">Belum ada order.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Suspend --}}
<div id="suspendModal" class="fixed inset-0 bg-black/50 z-50 items-center justify-center" style="display:none">
    <div class="bg-white dark:bg-neutral-800 rounded-xl p-6 w-full max-w-sm mx-4 shadow-xl">
        <h6 class="font-semibold text-lg mb-1">Suspend Teknisi</h6>
        <p class="text-sm text-secondary-light mb-4">Tulis alasan suspend untuk <strong>{{ $technician->user->name }}</strong></p>
        <form action="{{ route('bp-technicians.suspend', $technician) }}" method="POST">
            @csrf @method('PATCH')
            <div class="mb-4">
                <textarea name="reason" rows="3" class="form-control"
                    placeholder="Contoh: Sering tidak merespons order..." required></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn btn-danger-600 flex-1">Suspend</button>
                <button type="button" onclick="closeSuspendModal()" class="btn btn-neutral-200 flex-1">Batal</button>
            </div>
        </form>
    </div>
</div>

        {{-- Catatan Tambahan (kalau ada) --}}
        @if($technician->extra_note)
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-3 px-5">
                <h6 class="font-semibold text-sm mb-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:sticky-note" class="text-primary-600"></iconify-icon>
                    Catatan Tambahan
                </h6>
            </div>
            <div class="card-body p-5">
                <p class="text-sm">{{ $technician->extra_note }}</p>
            </div>
        </div>
        @endif

    </div>
</div>


{{-- ===== MODAL APPROVE ===== --}}
@if($technician->isPending())
<div id="approveModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center" style="display:none !important">
    <div class="bg-white dark:bg-neutral-800 rounded-xl p-6 w-full max-w-sm mx-4 shadow-xl">
        <h6 class="font-semibold text-lg mb-1">Approve Teknisi</h6>
        <p class="text-sm text-secondary-light mb-4">Tentukan grade untuk <strong>{{ $technician->user->name }}</strong></p>
        <form id="approveForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="form-label font-medium text-sm">Grade <span class="text-danger-600">*</span></label>
                <select name="grade" class="form-control">
                    <option value="">-- Pilih Grade --</option>
                    <option value="beginner">Beginner</option>
                    <option value="medium">Medium</option>
                    <option value="pro">Pro</option>
                </select>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn btn-success-600 flex-1">
                    <iconify-icon icon="lucide:check" class="mr-1"></iconify-icon> Approve
                </button>
                <button type="button" onclick="closeApproveModal()" class="btn btn-neutral-200 flex-1">Batal</button>
            </div>
        </form>
    </div>
</div>

{{-- ===== MODAL REJECT ===== --}}
<div id="rejectModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center" style="display:none !important">
    <div class="bg-white dark:bg-neutral-800 rounded-xl p-6 w-full max-w-sm mx-4 shadow-xl">
        <h6 class="font-semibold text-lg mb-1">Reject Teknisi</h6>
        <p class="text-sm text-secondary-light mb-4">Tuliskan alasan penolakan untuk <strong>{{ $technician->user->name }}</strong></p>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="form-label font-medium text-sm">Alasan <span class="text-danger-600">*</span></label>
                <textarea name="rejection_reason" rows="3" class="form-control"
                          placeholder="Tuliskan alasan penolakan..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn btn-danger-600 flex-1">
                    <iconify-icon icon="lucide:x" class="mr-1"></iconify-icon> Reject
                </button>
                <button type="button" onclick="closeRejectModal()" class="btn btn-neutral-200 flex-1">Batal</button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
    function openSuspendModal() {
    document.getElementById('suspendModal').style.display = 'flex';
}
function closeSuspendModal() {
    document.getElementById('suspendModal').style.display = 'none';
}
function openApproveModal(id) {
    document.getElementById('approveForm').action = `/bp-approvals/${id}/approve`;
    document.getElementById('approveModal').style.removeProperty('display');
}
function closeApproveModal() {
    document.getElementById('approveModal').style.display = 'none';
}
function openRejectModal(id) {
    document.getElementById('rejectForm').action = `/bp-approvals/${id}/reject`;
    document.getElementById('rejectModal').style.removeProperty('display');
}
function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}
</script>
@endpush