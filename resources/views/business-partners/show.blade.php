@extends('layouts.app')
@section('title', 'Detail Business Partner')
@section('page-title', 'Detail Business Partner')

@section('breadcrumb')
    <li><a href="{{ route('business-partners.index') }}" class="dark:text-white">Business Partner</a></li>
    <li class="font-medium dark:text-white">Detail</li>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">

    {{-- Info BP --}}
    <div class="lg:col-span-1">
        <div class="card border-0">
            <div class="card-body p-6 text-center">
                <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <iconify-icon icon="mage:store" class="text-purple-600 text-3xl"></iconify-icon>
                </div>
                <h6 class="text-lg font-semibold">{{ $businessPartner->name }}</h6>
                <p class="text-secondary-light text-sm">{{ $businessPartner->user->email }}</p>
                <span class="bg-purple-100 text-purple-600 px-3 py-1 rounded-full text-sm font-medium">Business Partner</span>

                <div class="mt-5 text-left flex flex-col gap-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-secondary-light">Kota</span>
                        <span class="font-medium">{{ $businessPartner->city ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-secondary-light">Provinsi</span>
                        <span class="font-medium">{{ $businessPartner->provience ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-secondary-light">Alamat</span>
                        <span class="font-medium text-right">{{ $businessPartner->address ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-secondary-light">Balance</span>
                        <span class="font-semibold text-success-600">Rp {{ number_format($businessPartner->balance, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-secondary-light">Status</span>
                        @if($businessPartner->user->is_active)
                            <span class="bg-success-100 text-success-600 px-2 py-px rounded-full text-xs">Aktif</span>
                        @else
                            <span class="bg-danger-100 text-danger-600 px-2 py-px rounded-full text-xs">Nonaktif</span>
                        @endif
                    </div>
                </div>

                <a href="{{ route('business-partners.edit', $businessPartner) }}"
                   class="btn btn-primary-600 w-full mt-5">
                    <iconify-icon icon="lucide:pencil" class="mr-1"></iconify-icon> Edit
                </a>
            </div>
        </div>
    </div>

    {{-- BP Services --}}
    <div class="lg:col-span-2">
        <div class="card border-0">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6 flex items-center justify-between">
                <h6 class="text-lg font-semibold mb-0">Layanan BP</h6>
            </div>
            <div class="card-body p-6">
                <div class="table-responsive">
                    <table class="table bordered-table style-two mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Jenis Layanan</th>
                                <th>Harga Dasar</th>
                                <th>Diskon</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($businessPartner->bpServices as $service)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $service->serviceType->name }}</td>
                                <td>Rp {{ number_format($service->base_service, 0, ',', '.') }}</td>
                                <td>{{ $service->discount }}%</td>
                                <td>
                                    @if($service->is_active)
                                        <span class="bg-success-100 text-success-600 px-3 py-1 rounded-full text-sm">Aktif</span>
                                    @else
                                        <span class="bg-danger-100 text-danger-600 px-3 py-1 rounded-full text-sm">Nonaktif</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary-light py-6">Belum ada layanan terdaftar.</td>
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