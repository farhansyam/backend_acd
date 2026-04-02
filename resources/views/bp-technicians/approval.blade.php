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
    <div class="card-body p-6">

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

{{-- Modal Approve --}}
<div id="approveModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50" style="display:none">
    <div class="bg-white dark:bg-neutral-800 rounded-xl p-6 w-full max-w-sm mx-4">
        <h6 class="font-semibold text-lg mb-4">Tentukan Grade Teknisi</h6>
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
                <button type="submit" class="btn btn-success-600 flex-1">Approve</button>
                <button type="button" onclick="closeApproveModal()" class="btn btn-neutral-200 flex-1">Batal</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Reject --}}
<div id="rejectModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50" style="display:none">
    <div class="bg-white dark:bg-neutral-800 rounded-xl p-6 w-full max-w-sm mx-4">
        <h6 class="font-semibold text-lg mb-4">Alasan Penolakan</h6>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="form-label font-medium text-sm">Alasan <span class="text-danger-600">*</span></label>
                <textarea name="rejection_reason" rows="3" class="form-control"
                          placeholder="Tuliskan alasan penolakan..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn btn-danger-600 flex-1">Reject</button>
                <button type="button" onclick="closeRejectModal()" class="btn btn-neutral-200 flex-1">Batal</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openApproveModal(id) {
    document.getElementById('approveForm').action = `/bp-approvals/${id}/approve`;
    document.getElementById('approveModal').style.display = 'flex';
}
function closeApproveModal() {
    document.getElementById('approveModal').style.display = 'none';
}
function openRejectModal(id) {
    document.getElementById('rejectForm').action = `/bp-approvals/${id}/reject`;
    document.getElementById('rejectModal').style.display = 'flex';
}
function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}
</script>
@endpush