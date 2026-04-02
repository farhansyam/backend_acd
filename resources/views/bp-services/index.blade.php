@extends('layouts.app')
@section('title', 'BP Services')
@section('page-title', 'BP Services')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">BP Services</li>
@endsection

@section('content')
<div class="card border-0 mt-6">
    <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6 flex items-center justify-between">
        <h6 class="text-lg font-semibold mb-0">Daftar BP Services</h6>
        <a href="{{ route('bp-services.create') }}" class="btn btn-primary-600 flex items-center gap-2">
            <iconify-icon icon="lucide:plus"></iconify-icon> Tambah Service
        </a>
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
                        <th>Banner</th>
                        @if(auth()->user()->role === 'adminsuper')
                            <th>Business Partner</th>
                        @endif
                        <th>Jenis Layanan</th>
                        <th>Harga Dasar</th>
                        <th>Diskon</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bpServices as $service)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            @if($service->banner)
                                <img src="{{ asset('storage/' . $service->banner) }}"
                                     alt="banner"
                                     class="w-16 h-10 object-cover rounded">
                            @else
                                <div class="w-16 h-10 bg-neutral-100 dark:bg-neutral-600 rounded flex items-center justify-center">
                                    <iconify-icon icon="lucide:image" class="text-neutral-400"></iconify-icon>
                                </div>
                            @endif
                        </td>
                        @if(auth()->user()->role === 'adminsuper')
                            <td>{{ $service->businessPartner->name }}</td>
                        @endif
                        <td>{{ $service->serviceType->name }}</td>
                        <td>Rp {{ number_format($service->base_service, 0, ',', '.') }}</td>
                        <td>{{ $service->discount }}%</td>
                        <td>
                            @if($service->is_active)
                                <span class="bg-success-100 text-success-600 px-3 py-1 rounded-full text-sm font-medium">Aktif</span>
                            @else
                                <span class="bg-danger-100 text-danger-600 px-3 py-1 rounded-full text-sm font-medium">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('bp-services.show', $service) }}"
                                   class="w-8 h-8 bg-info-100 text-info-600 rounded flex items-center justify-center">
                                    <iconify-icon icon="lucide:eye"></iconify-icon>
                                </a>
                                <a href="{{ route('bp-services.edit', $service) }}"
                                   class="w-8 h-8 bg-warning-100 text-warning-600 rounded flex items-center justify-center">
                                    <iconify-icon icon="lucide:pencil"></iconify-icon>
                                </a>
                                <form action="{{ route('bp-services.destroy', $service) }}" method="POST"
                                      onsubmit="return confirm('Yakin hapus service ini?')">
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
                        <td colspan="8" class="text-center text-secondary-light py-6">Belum ada BP Service.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $bpServices->links() }}
        </div>

    </div>
</div>
@endsection