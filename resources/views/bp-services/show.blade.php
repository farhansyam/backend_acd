@extends('layouts.app')
@section('title', 'Detail BP Service')
@section('page-title', 'Detail BP Service')

@section('breadcrumb')
    <li><a href="{{ route('bp-services.index') }}" class="dark:text-white">BP Services</a></li>
    <li class="font-medium dark:text-white">Detail</li>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">

    {{-- Banner --}}
    <div class="lg:col-span-1">
        <div class="card border-0">
            <div class="card-body p-0 overflow-hidden rounded-lg">
                @if($bpService->banner)
                    <img src="{{ asset('storage/' . $bpService->banner) }}"
                         alt="banner" class="w-full h-56 object-cover">
                @else
                    <div class="w-full h-56 bg-neutral-100 dark:bg-neutral-700 flex flex-col items-center justify-center">
                        <iconify-icon icon="lucide:image" class="text-neutral-400 text-4xl mb-2"></iconify-icon>
                        <span class="text-sm text-secondary-light">Tidak ada banner</span>
                    </div>
                @endif
            </div>
            <div class="card-body p-5">
                @if($bpService->is_active)
                    <span class="bg-success-100 text-success-600 px-3 py-1 rounded-full text-sm font-medium">Aktif</span>
                @else
                    <span class="bg-danger-100 text-danger-600 px-3 py-1 rounded-full text-sm font-medium">Nonaktif</span>
                @endif

                <div class="flex gap-2 mt-4">
                    <a href="{{ route('bp-services.edit', $bpService) }}"
                       class="btn btn-warning-600 flex-1 text-center">
                        <iconify-icon icon="lucide:pencil" class="mr-1"></iconify-icon> Edit
                    </a>
                    <form action="{{ route('bp-services.destroy', $bpService) }}" method="POST"
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

    {{-- Detail --}}
    <div class="lg:col-span-2">
        <div class="card border-0 h-full">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6">
                <h6 class="text-lg font-semibold mb-0">Detail Service</h6>
            </div>
            <div class="card-body p-6">
                <div class="flex flex-col gap-4">

                    @if(auth()->user()->role === 'adminsuper')
                    <div class="flex justify-between py-3 border-b border-neutral-100 dark:border-neutral-600">
                        <span class="text-secondary-light text-sm">Business Partner</span>
                        <span class="font-medium text-sm">{{ $bpService->businessPartner->name }}</span>
                    </div>
                    @endif

                    <div class="flex justify-between py-3 border-b border-neutral-100 dark:border-neutral-600">
                        <span class="text-secondary-light text-sm">Jenis Layanan</span>
                        <span class="font-medium text-sm">{{ $bpService->serviceType->name }}</span>
                    </div>

                    <div class="flex justify-between py-3 border-b border-neutral-100 dark:border-neutral-600">
                        <span class="text-secondary-light text-sm">Harga Dasar</span>
                        <span class="font-semibold text-sm">Rp {{ number_format($bpService->base_service, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between py-3 border-b border-neutral-100 dark:border-neutral-600">
                        <span class="text-secondary-light text-sm">Diskon</span>
                        <span class="font-medium text-sm">{{ $bpService->discount }}%</span>
                    </div>

                    <div class="flex justify-between py-3 border-b border-neutral-100 dark:border-neutral-600">
                        <span class="text-secondary-light text-sm">Harga Setelah Diskon</span>
                        <span class="font-semibold text-success-600 text-sm">
                            Rp {{ number_format($bpService->base_service - ($bpService->base_service * $bpService->discount / 100), 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex justify-between py-3">
                        <span class="text-secondary-light text-sm">Dibuat</span>
                        <span class="font-medium text-sm">{{ $bpService->created_at->format('d M Y, H:i') }}</span>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>
@endsection