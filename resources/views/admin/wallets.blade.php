@extends('layouts.app')
@section('title', 'Wallet')
@section('page-title', 'Wallet')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">Wallet</li>
@endsection

@section('content')

{{-- Summary Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-6 mb-6">
    <div class="card border-0">
        <div class="card-body p-6 flex items-center gap-4">
            <div class="w-14 h-14 bg-primary-100 rounded-xl flex items-center justify-center">
                <iconify-icon icon="lucide:users" class="text-primary-600 text-2xl"></iconify-icon>
            </div>
            <div>
                <p class="text-sm text-secondary-light mb-1">Total Saldo Teknisi</p>
                <p class="text-2xl font-bold text-primary-600">Rp {{ number_format($totalTechBalance, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
    <div class="card border-0">
        <div class="card-body p-6 flex items-center gap-4">
            <div class="w-14 h-14 bg-success-100 rounded-xl flex items-center justify-center">
                <iconify-icon icon="lucide:building-2" class="text-success-600 text-2xl"></iconify-icon>
            </div>
            <div>
                <p class="text-sm text-secondary-light mb-1">Total Saldo BP</p>
                <p class="text-2xl font-bold text-success-600">Rp {{ number_format($totalBpBalance, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Saldo Teknisi --}}
    <div class="card border-0">
        <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6">
            <h6 class="font-semibold mb-0">Saldo Teknisi</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table bordered-table style-two mb-0">
                    <thead>
                        <tr>
                            <th>Teknisi</th>
                            <th>Grade</th>
                            <th>Saldo</th>
                            <th>Ditahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($technicians as $tech)
                        <tr>
                            <td>
                                <p class="font-medium text-sm">{{ $tech->user->name ?? '-' }}</p>
                                <p class="text-xs text-secondary-light">{{ $tech->city }}</p>
                            </td>
                            <td>
                                <span class="text-xs capitalize font-medium">{{ $tech->grade }}</span>
                            </td>
                            <td class="font-bold text-sm">
                                Rp {{ number_format($tech->balance, 0, ',', '.') }}
                            </td>
                            <td class="text-sm text-warning-600">
                                Rp {{ number_format($tech->balance_hold, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-secondary-light py-4">Belum ada data.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Saldo BP --}}
    <div class="card border-0">
        <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6">
            <h6 class="font-semibold mb-0">Saldo Business Partner</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table bordered-table style-two mb-0">
                    <thead>
                        <tr>
                            <th>Business Partner</th>
                            <th>Kota</th>
                            <th>Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($businessPartners as $bp)
                        <tr>
                            <td class="font-medium text-sm">{{ $bp->name }}</td>
                            <td class="text-sm text-secondary-light">{{ $bp->city ?? '-' }}</td>
                            <td class="font-bold text-sm">
                                Rp {{ number_format($bp->balance, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-secondary-light py-4">Belum ada data.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection