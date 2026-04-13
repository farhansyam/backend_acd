@extends('layouts.app')
@section('title', 'Penarikan Saldo')
@section('page-title', 'Penarikan Saldo')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">Penarikan Saldo</li>
@endsection

@section('content')
<div class="card border-0 mt-6">
    <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6 flex items-center justify-between">
        <h6 class="text-lg font-semibold mb-0">Daftar Permintaan Penarikan</h6>
        <span class="text-sm text-secondary-light">Total: {{ $withdrawals->total() }}</span>
    </div>
    <div class="card-body p-6">

        @if(session('success'))
            <div class="bg-success-100 text-success-600 px-4 py-3 rounded mb-4 flex items-center gap-2">
                <iconify-icon icon="lucide:check-circle"></iconify-icon>
                {{ session('success') }}
            </div>
        @endif

        <div class="table-responsive">
            <table class="table bordered-table style-two mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Teknisi</th>
                        <th>Nominal</th>
                        <th>Bank</th>
                        <th>No. Rekening</th>
                        <th>A/N</th>
                        <th>Status</th>
                        <th>Diajukan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($withdrawals as $withdrawal)
                    @php
                        $statusMap = [
                            'pending'  => ['class' => 'bg-warning-100 text-warning-600', 'label' => 'Menunggu'],
                            'approved' => ['class' => 'bg-success-100 text-success-600', 'label' => 'Disetujui'],
                            'rejected' => ['class' => 'bg-danger-100 text-danger-600', 'label' => 'Ditolak'],
                        ];
                        $s = $statusMap[$withdrawal->status];
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <p class="font-medium">{{ $withdrawal->technician->user->name ?? '-' }}</p>
                            <p class="text-xs text-secondary-light capitalize">{{ $withdrawal->technician->grade }}</p>
                        </td>
                        <td class="font-bold">Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</td>
                        <td>{{ $withdrawal->bank_name }}</td>
                        <td>{{ $withdrawal->account_number }}</td>
                        <td>{{ $withdrawal->account_name }}</td>
                        <td>
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $s['class'] }}">
                                {{ $s['label'] }}
                            </span>
                        </td>
                        <td class="text-sm text-secondary-light">
                            {{ $withdrawal->created_at->format('d M Y H:i') }}
                        </td>
                        <td>
                            @if($withdrawal->status === 'pending')
                            <div class="flex items-center gap-2">
                                {{-- Approve --}}
                                <form action="{{ route('withdrawals.approve', $withdrawal) }}" method="POST"
                                      onsubmit="return confirm('Setujui penarikan ini?')">
                                    @csrf
                                    <button type="submit"
                                            class="w-8 h-8 bg-success-100 text-success-600 rounded flex items-center justify-center"
                                            title="Approve">
                                        <iconify-icon icon="lucide:check"></iconify-icon>
                                    </button>
                                </form>

                                {{-- Reject --}}
                                <button onclick="openRejectModal({{ $withdrawal->id }})"
                                        class="w-8 h-8 bg-danger-100 text-danger-600 rounded flex items-center justify-center"
                                        title="Reject">
                                    <iconify-icon icon="lucide:x"></iconify-icon>
                                </button>
                            </div>
                            @else
                                <span class="text-xs text-secondary-light">
                                    {{ $withdrawal->reviewed_at?->format('d M Y') ?? '-' }}
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-secondary-light py-6">Belum ada permintaan penarikan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $withdrawals->links() }}
        </div>
    </div>
</div>

{{-- Modal Reject --}}
<div id="rejectModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center" style="display:none !important">
    <div class="bg-white dark:bg-neutral-800 rounded-xl p-6 w-full max-w-sm mx-4 shadow-xl">
        <h6 class="font-semibold text-lg mb-1">Tolak Penarikan</h6>
        <p class="text-sm text-secondary-light mb-4">Tulis alasan penolakan</p>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="form-label fw-semibold text-sm">Alasan <span class="text-danger-600">*</span></label>
                <textarea name="rejection_reason" rows="3" class="form-control"
                    placeholder="Contoh: Data rekening tidak valid..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn btn-danger-600 flex-1">Tolak</button>
                <button type="button" onclick="closeRejectModal()" class="btn btn-neutral-200 flex-1">Batal</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openRejectModal(id) {
    document.getElementById('rejectForm').action = `/withdrawals/${id}/reject`;
    document.getElementById('rejectModal').style.removeProperty('display');
}
function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}
</script>
@endpush
@endsection