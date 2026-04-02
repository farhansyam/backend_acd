@extends('layouts.app')
@section('title', 'Business Partner')
@section('page-title', 'Business Partner')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">Business Partner</li>
@endsection

@section('content')
<div class="card border-0 mt-6">
    <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6 flex items-center justify-between">
        <h6 class="text-lg font-semibold mb-0">Daftar Business Partner</h6>
        <a href="{{ route('business-partners.create') }}" class="btn btn-primary-600 flex items-center gap-2">
            <iconify-icon icon="lucide:plus"></iconify-icon> Tambah BP
        </a>
    </div>
    <div class="card-body p-6">

        @if(session('success'))
            <div class="bg-success-100 text-success-600 px-4 py-3 rounded mb-4">
                {{ session('success') }}
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
                        <th>Provinsi</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($businessPartners as $bp)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $bp->name }}</td>
                        <td>{{ $bp->user->email }}</td>
                        <td>{{ $bp->city ?? '-' }}</td>
                        <td>{{ $bp->provience ?? '-' }}</td>
                        <td>Rp {{ number_format($bp->balance, 0, ',', '.') }}</td>
                        <td>
                            @if($bp->user->is_active)
                                <span class="bg-success-100 text-success-600 px-3 py-1 rounded-full text-sm font-medium">Aktif</span>
                            @else
                                <span class="bg-danger-100 text-danger-600 px-3 py-1 rounded-full text-sm font-medium">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('business-partners.show', $bp) }}"
                                   class="w-8 h-8 bg-info-100 text-info-600 rounded flex items-center justify-center">
                                    <iconify-icon icon="lucide:eye"></iconify-icon>
                                </a>
                                <a href="{{ route('business-partners.edit', $bp) }}"
                                   class="w-8 h-8 bg-warning-100 text-warning-600 rounded flex items-center justify-center">
                                    <iconify-icon icon="lucide:pencil"></iconify-icon>
                                </a>
                                <form action="{{ route('business-partners.destroy', $bp) }}" method="POST"
                                      onsubmit="return confirm('Yakin hapus BP ini? User terkait juga akan terhapus.')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="w-8 h-8 bg-danger-100 text-danger-600 rounded flex items-center justify-center">
                                        <iconify-icon icon="lucide:trash-2"></iconify-icon>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-secondary-light py-6">Belum ada data Business Partner.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $businessPartners->links() }}
        </div>

    </div>
</div>
@endsection