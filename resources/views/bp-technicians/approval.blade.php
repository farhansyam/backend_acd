@extends('layouts.app')
@section('title', 'Approval Teknisi')
@section('page-title', 'Approval Teknisi')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">Approval Teknisi</li>
@endsection

@section('content')
<div class="card border-0 mt-6">
    <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6 flex items-center justify-between">
        <h6 class="text-lg font-semibold mb-0">Teknisi Menunggu Approval</h6>
        <a href="{{ route('bp-technicians.index') }}" class="btn btn-neutral-200 flex items-center gap-2">
            <iconify-icon icon="lucide:users"></iconify-icon> Teknisi Aktif
        </a>
    </div>
    <div class="card-body p-3">

        @if(session('success'))
            <div class="bg-success-100 text-success-600 px-4 py-3 rounded mb-4 flex items-center gap-2">
                <iconify-icon icon="lucide:check-circle"></iconify-icon> {{ session('success') }}
            </div>
        @endif

        <div class="table-responsive">
            <table class="table bordered-table style-two mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Kota</th>
                        <th>Dokumen</th>
                        <th>Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pending as $tech)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="font-medium">{{ $tech->user->name }}</td>
                        <td>{{ $tech->user->email }}</td>
                        <td>{{ $tech->city ?? '-' }}</td>
                        <td>
                            <div class="flex gap-1 flex-wrap">
                                @if($tech->ktp_photo)
                                    <a href="{{ asset('storage/'.$tech->ktp_photo) }}" target="_blank"
                                       class="bg-primary-100 text-primary-600 px-2 py-px rounded text-xs">KTP</a>
                                @endif
                                @if($tech->selfie_photo)
                                    <a href="{{ asset('storage/'.$tech->selfie_photo) }}" target="_blank"
                                       class="bg-info-100 text-info-600 px-2 py-px rounded text-xs">Selfie</a>
                                @endif
                                @if($tech->skck_file)
                                    <a href="{{ asset('storage/'.$tech->skck_file) }}" target="_blank"
                                       class="bg-warning-100 text-warning-600 px-2 py-px rounded text-xs">SKCK</a>
                                @endif
                                @if($tech->certificate)
                                    <a href="{{ asset('storage/'.$tech->certificate) }}" target="_blank"
                                       class="bg-success-100 text-success-600 px-2 py-px rounded text-xs">Sertifikat</a>
                                @endif
                            </div>
                        </td>
                        <td>{{ $tech->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                {{-- Approve --}}
                                <button onclick="openApproveModal({{ $tech->id }})"
                                        class="w-8 h-8 bg-success-100 text-success-600 rounded flex items-center justify-center"
                                        title="Approve">
                                    <iconify-icon icon="lucide:check"></iconify-icon>
                                </button>
                                {{-- Reject --}}
                                <button onclick="openRejectModal({{ $tech->id }})"
                                        class="w-8 h-8 bg-danger-100 text-danger-600 rounded flex items-center justify-center"
                                        title="Reject">
                                    <iconify-icon icon="lucide:x"></iconify-icon>
                                </button>
                                {{-- Detail --}}
                                <a href="{{ route('bp-technicians.show', $tech) }}"
                                   class="w-8 h-8 bg-info-100 text-info-600 rounded flex items-center justify-center"
                                   title="Detail">
                                    <iconify-icon icon="lucide:eye"></iconify-icon>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-secondary-light py-10">
                            <iconify-icon icon="lucide:check-circle" class="text-4xl mb-2 block text-success-400"></iconify-icon>
                            Tidak ada teknisi yang menunggu approval.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $pending->links() }}</div>

    </div>
</div>

{{-- Backdrop --}}
<div id="modalBackdrop"
     class="hidden fixed inset-0 z-40 bg-black bg-opacity-50"
     onclick="closeAllModals()"></div>

{{-- Modal Approve --}}
<div id="approveModal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-xl w-full max-w-xs">
        <div class="flex items-center justify-between px-4 py-3 border-b border-neutral-200 dark:border-neutral-600">
            <h3 class="text-sm font-semibold text-neutral-800 dark:text-white">Tentukan Grade Teknisi</h3>
            <button onclick="closeAllModals()"
                    class="w-7 h-7 flex items-center justify-center rounded hover:bg-neutral-100 dark:hover:bg-neutral-700 text-neutral-400">
                <iconify-icon icon="lucide:x"></iconify-icon>
            </button>
        </div>
        <div class="px-4 py-3">
            <form id="approveForm" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="block text-xs font-medium mb-1 text-neutral-700 dark:text-neutral-200">
                        Grade <span class="text-red-500">*</span>
                    </label>
                    <select name="grade" class="form-control text-xs py-1.5 w-full">
                        <option value="">-- Pilih Grade --</option>
                        <option value="beginner">Beginner</option>
                        <option value="medium">Medium</option>
                        <option value="pro">Pro</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-success-600 flex-1 py-1.5 text-xs">Approve</button>
                    <button type="button" onclick="closeAllModals()" class="btn btn-neutral-200 flex-1 py-1.5 text-xs">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Reject --}}
<div id="rejectModal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-xl w-full max-w-xs">
        <div class="flex items-center justify-between px-4 py-3 border-b border-neutral-200 dark:border-neutral-600">
            <h3 class="text-sm font-semibold text-neutral-800 dark:text-white">Alasan Penolakan</h3>
            <button onclick="closeAllModals()"
                    class="w-7 h-7 flex items-center justify-center rounded hover:bg-neutral-100 dark:hover:bg-neutral-700 text-neutral-400">
                <iconify-icon icon="lucide:x"></iconify-icon>
            </button>
        </div>
        <div class="px-4 py-3">
            <form id="rejectForm" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="block text-xs font-medium mb-1 text-neutral-700 dark:text-neutral-200">
                        Alasan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="rejection_reason" rows="3"
                              class="form-control text-xs w-full"
                              placeholder="Tuliskan alasan penolakan..."></textarea>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-danger-600 flex-1 py-1.5 text-xs">Reject</button>
                    <button type="button" onclick="closeAllModals()" class="btn btn-neutral-200 flex-1 py-1.5 text-xs">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openApproveModal(id) {
        document.getElementById('approveForm').action = `/bp-approvals/${id}/approve`;
        document.getElementById('modalBackdrop').classList.remove('hidden');
        document.getElementById('approveModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function openRejectModal(id) {
        document.getElementById('rejectForm').action = `/bp-approvals/${id}/reject`;
        document.getElementById('modalBackdrop').classList.remove('hidden');
        document.getElementById('rejectModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeAllModals() {
        document.getElementById('approveModal').classList.add('hidden');
        document.getElementById('rejectModal').classList.add('hidden');
        document.getElementById('modalBackdrop').classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Tutup modal dengan tombol ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAllModals();
    });
</script>
@endpush