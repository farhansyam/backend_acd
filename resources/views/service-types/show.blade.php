@extends('layouts.app')
@section('title', 'Detail Jenis Layanan')
@section('page-title', 'Detail Jenis Layanan')

@section('breadcrumb')
    <li><a href="{{ route('service-types.index') }}" class="dark:text-white">Jenis Layanan</a></li>
    <li class="font-medium dark:text-white">Detail</li>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">

    {{-- Info Service Type --}}
    <div class="lg:col-span-1">
        <div class="card border-0">
            <div class="card-body p-6">
                <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mb-4">
                    <iconify-icon icon="solar:document-text-outline" class="text-primary-600 text-3xl"></iconify-icon>
                </div>
                <h6 class="text-lg font-semibold mb-1">{{ $serviceType->name }}</h6>
                <p class="text-secondary-light text-sm mb-4">{{ $serviceType->description ?? 'Tidak ada deskripsi.' }}</p>

                <div class="flex flex-col gap-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-secondary-light">Status</span>
                        @if($serviceType->is_active)
                            <span class="bg-success-100 text-success-600 px-2 py-px rounded-full text-xs font-medium">Aktif</span>
                        @else
                            <span class="bg-danger-100 text-danger-600 px-2 py-px rounded-full text-xs font-medium">Nonaktif</span>
                        @endif
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-secondary-light">Digunakan</span>
                        <span class="font-medium">{{ $serviceType->bpServices->count() }} BP</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-secondary-light">Dibuat</span>
                        <span class="font-medium">{{ $serviceType->created_at->format('d M Y') }}</span>
                    </div>
                </div>

                <div class="flex gap-2 mt-5">
                    <a href="{{ route('service-types.edit', $serviceType) }}"
                       class="btn btn-primary-600 flex-1 text-center">
                        <iconify-icon icon="lucide:pencil" class="mr-1"></iconify-icon> Edit
                    </a>
                    <form action="{{ route('service-types.destroy', $serviceType) }}" method="POST"
                          onsubmit="return confirm('Yakin hapus?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger-600">
                            <iconify-icon icon="lucide:trash-2"></iconify-icon>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- BP yang menggunakan layanan ini --}}
    <div class="lg:col-span-2">
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6">
                <h6 class="text-lg font-semibold mb-0">Business Partner yang Menggunakan</h6>
            </div>
            <div class="card-body p-6">
                <div class="table-responsive">
                    <table class="table bordered-table style-two mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama BP</th>
                                <th>Harga Dasar</th>
                                <th>Diskon</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($serviceType->bpServices as $bpService)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $bpService->businessPartner->name }}</td>
                                <td>Rp {{ number_format($bpService->base_service, 0, ',', '.') }}</td>
                                <td>{{ $bpService->discount }}%</td>
                                <td>
                                    @if($bpService->is_active)
                                        <span class="bg-success-100 text-success-600 px-3 py-1 rounded-full text-sm">Aktif</span>
                                    @else
                                        <span class="bg-danger-100 text-danger-600 px-3 py-1 rounded-full text-sm">Nonaktif</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary-light py-6">
                                    Belum ada BP yang menggunakan layanan ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection