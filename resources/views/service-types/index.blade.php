@extends('layouts.app')
@section('title', 'Jenis Layanan')
@section('page-title', 'Jenis Layanan')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">Jenis Layanan</li>
@endsection

@section('content')
<div class="card border-0 mt-6">
    <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6 flex items-center justify-between">
        <h6 class="text-lg font-semibold mb-0">Daftar Jenis Layanan</h6>
        <a href="{{ route('service-types.create') }}" class="btn btn-primary-600 flex items-center gap-2">
            <iconify-icon icon="lucide:plus"></iconify-icon> Tambah Jenis Layanan
        </a>
    </div>
    <div class="card-body p-6">

        @if(session('success'))
            <div class="bg-success-100 text-success-600 px-4 py-3 rounded mb-4 flex items-center gap-2">
                <iconify-icon icon="lucide:check-circle"></iconify-icon>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-danger-100 text-danger-600 px-4 py-3 rounded mb-4 flex items-center gap-2">
                <iconify-icon icon="lucide:alert-circle"></iconify-icon>
                {{ session('error') }}
            </div>
        @endif

        <div class="table-responsive">
            <table class="table bordered-table style-two mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Layanan</th>
                        <th>Deskripsi</th>
                        <th>Digunakan BP</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($serviceTypes as $serviceType)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="font-medium">{{ $serviceType->name }}</td>
                        <td class="text-secondary-light">
                            {{ $serviceType->description ? Str::limit($serviceType->description, 60) : '-' }}
                        </td>
                        <td>
                            <span class="bg-primary-100 text-primary-600 px-2 py-1 rounded text-sm font-medium">
                                {{ $serviceType->bpServices->count() }} BP
                            </span>
                        </td>
                        <td>
                            @if($serviceType->is_active)
                                <span class="bg-success-100 text-success-600 px-3 py-1 rounded-full text-sm font-medium">Aktif</span>
                            @else
                                <span class="bg-danger-100 text-danger-600 px-3 py-1 rounded-full text-sm font-medium">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('service-types.show', $serviceType) }}"
                                   class="w-8 h-8 bg-info-100 text-info-600 rounded flex items-center justify-center"
                                   title="Detail">
                                    <iconify-icon icon="lucide:eye"></iconify-icon>
                                </a>
                                <a href="{{ route('service-types.edit', $serviceType) }}"
                                   class="w-8 h-8 bg-warning-100 text-warning-600 rounded flex items-center justify-center"
                                   title="Edit">
                                    <iconify-icon icon="lucide:pencil"></iconify-icon>
                                </a>
                                <form action="{{ route('service-types.destroy', $serviceType) }}" method="POST"
                                      onsubmit="return confirm('Yakin hapus jenis layanan ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="w-8 h-8 bg-danger-100 text-danger-600 rounded flex items-center justify-center"
                                            title="Hapus">
                                        <iconify-icon icon="lucide:trash-2"></iconify-icon>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-secondary-light py-10">
                            <iconify-icon icon="lucide:inbox" class="text-4xl mb-2 block"></iconify-icon>
                            Belum ada jenis layanan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $serviceTypes->links() }}
        </div>

    </div>
</div>
@endsection