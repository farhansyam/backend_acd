@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">CRM</li>
@endsection

@section('content')

{{-- Stat Cards --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-6">
    <div class="lg:col-span-12 2xl:col-span-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">

            <div class="card px-4 py-5 shadow-2 rounded-lg border-gray-200 dark:border-neutral-600 h-full bg-gradient-to-l from-primary-600/10 to-bg-white">
                <div class="card-body p-0">
                    <div class="flex flex-wrap items-center justify-between gap-1 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="mb-0 w-[44px] h-[44px] bg-primary-600 shrink-0 text-white flex justify-center items-center rounded-full h6">
                                <iconify-icon icon="mingcute:user-follow-fill" class="icon"></iconify-icon>
                            </span>
                            <div>
                                <span class="mb-2 font-medium text-secondary-light text-sm">Total Order</span>
                                <h6 class="font-semibold">1,200</h6>
                            </div>
                        </div>
                        <div id="new-user-chart" class="remove-tooltip-title rounded-tooltip-value"></div>
                    </div>
                    <p class="text-sm mb-0">Naik <span class="bg-success-100 dark:bg-success-600/25 px-1 py-px rounded font-medium text-success-600 dark:text-success-400 text-sm">+50</span> minggu ini</p>
                </div>
            </div>

            <div class="card px-4 py-5 shadow-2 rounded-lg border-gray-200 dark:border-neutral-600 h-full bg-gradient-to-l from-success-600/10 to-bg-white">
                <div class="card-body p-0">
                    <div class="flex flex-wrap items-center justify-between gap-1 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="mb-0 w-[44px] h-[44px] bg-success-600 shrink-0 text-white flex justify-center items-center rounded-full h6">
                                <iconify-icon icon="solar:user-id-outline" class="icon"></iconify-icon>
                            </span>
                            <div>
                                <span class="mb-2 font-medium text-secondary-light text-sm">Teknisi Aktif</span>
                                <h6 class="font-semibold">84</h6>
                            </div>
                        </div>
                        <div id="active-user-chart" class="remove-tooltip-title rounded-tooltip-value"></div>
                    </div>
                    <p class="text-sm mb-0">Naik <span class="bg-success-100 dark:bg-success-600/25 px-1 py-px rounded font-medium text-success-600 dark:text-success-400 text-sm">+5</span> minggu ini</p>
                </div>
            </div>

            <div class="card px-4 py-5 shadow-2 rounded-lg border-gray-200 dark:border-neutral-600 h-full bg-gradient-to-l from-warning-600/10 to-bg-white">
                <div class="card-body p-0">
                    <div class="flex flex-wrap items-center justify-between gap-1 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="mb-0 w-[44px] h-[44px] bg-warning-600 text-white shrink-0 flex justify-center items-center rounded-full h6">
                                <iconify-icon icon="iconamoon:discount-fill" class="icon"></iconify-icon>
                            </span>
                            <div>
                                <span class="mb-2 font-medium text-secondary-light text-sm">Total Pendapatan</span>
                                <h6 class="font-semibold">Rp 48.500.000</h6>
                            </div>
                        </div>
                        <div id="total-sales-chart" class="remove-tooltip-title rounded-tooltip-value"></div>
                    </div>
                    <p class="text-sm mb-0">Turun <span class="bg-danger-100 dark:bg-danger-600/25 px-1 py-px rounded font-medium text-danger-600 dark:text-danger-400 text-sm">-Rp 2jt</span> minggu ini</p>
                </div>
            </div>

            <div class="card px-4 py-5 shadow-2 rounded-lg border-gray-200 dark:border-neutral-600 h-full bg-gradient-to-l from-purple-600/10 to-bg-white">
                <div class="card-body p-0">
                    <div class="flex flex-wrap items-center justify-between gap-1 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="mb-0 w-[44px] h-[44px] bg-purple-600 text-white shrink-0 flex justify-center items-center rounded-full h6">
                                <iconify-icon icon="mage:store" class="icon"></iconify-icon>
                            </span>
                            <div>
                                <span class="mb-2 font-medium text-secondary-light text-sm">Business Partner</span>
                                <h6 class="font-semibold">12</h6>
                            </div>
                        </div>
                        <div id="conversion-user-chart" class="remove-tooltip-title rounded-tooltip-value"></div>
                    </div>
                    <p class="text-sm mb-0">Naik <span class="bg-success-100 dark:bg-success-600/25 px-1 py-px rounded font-medium text-success-600 dark:text-success-400 text-sm">+2</span> bulan ini</p>
                </div>
            </div>

            <div class="card px-4 py-5 shadow-2 rounded-lg border-gray-200 dark:border-neutral-600 h-full bg-gradient-to-l from-pink-600/10 to-bg-white">
                <div class="card-body p-0">
                    <div class="flex flex-wrap items-center justify-between gap-1 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="mb-0 w-[44px] h-[44px] bg-pink-600 text-white shrink-0 flex justify-center items-center rounded-full h6">
                                <iconify-icon icon="mage:message-question-mark-round" class="icon"></iconify-icon>
                            </span>
                            <div>
                                <span class="mb-2 font-medium text-secondary-light text-sm">Komplain</span>
                                <h6 class="font-semibold">8</h6>
                            </div>
                        </div>
                        <div id="leads-chart" class="remove-tooltip-title rounded-tooltip-value"></div>
                    </div>
                    <p class="text-sm mb-0">Naik <span class="bg-danger-100 dark:bg-danger-600/25 px-1 py-px rounded font-medium text-danger-600 dark:text-danger-400 text-sm">+3</span> minggu ini</p>
                </div>
            </div>

            <div class="card px-4 py-5 shadow-2 rounded-lg border-gray-200 dark:border-neutral-600 h-full bg-gradient-to-l from-cyan-600/10 to-bg-white">
                <div class="card-body p-0">
                    <div class="flex flex-wrap items-center justify-between gap-1 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="mb-0 w-[44px] h-[44px] bg-cyan-600 text-white shrink-0 flex justify-center items-center rounded-full h6">
                                <iconify-icon icon="solar:card-transfer-outline" class="icon"></iconify-icon>
                            </span>
                            <div>
                                <span class="mb-2 font-medium text-secondary-light text-sm">Withdrawal Pending</span>
                                <h6 class="font-semibold">5</h6>
                            </div>
                        </div>
                        <div id="total-profit-chart" class="remove-tooltip-title rounded-tooltip-value"></div>
                    </div>
                    <p class="text-sm mb-0">Naik <span class="bg-success-100 dark:bg-success-600/25 px-1 py-px rounded font-medium text-success-600 dark:text-success-400 text-sm">+2</span> minggu ini</p>
                </div>
            </div>

        </div>
    </div>

    {{-- Revenue Growth --}}
    <div class="lg:col-span-12 2xl:col-span-4">
        <div class="card h-full rounded-lg border-0">
            <div class="card-body p-6">
                <div class="flex items-center flex-wrap gap-2 justify-between">
                    <div>
                        <h6 class="mb-2 font-bold text-lg">Pertumbuhan Pendapatan</h6>
                        <span class="text-sm font-medium text-secondary-light">Laporan Mingguan</span>
                    </div>
                    <div class="text-end">
                        <h6 class="mb-2 font-bold text-lg">Rp 48.500.000</h6>
                        <span class="bg-success-100 dark:bg-success-600/25 px-3 py-1 rounded font-medium text-success-600 dark:text-success-400 text-sm">+Rp 5jt</span>
                    </div>
                </div>
                <div id="revenue-chart" class="mt-0"></div>
            </div>
        </div>
    </div>

    {{-- Order Terbaru --}}
    <div class="lg:col-span-12 2xl:col-span-8">
        <div class="card h-full border-0 overflow-hidden">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6 flex items-center justify-between">
                <h6 class="text-lg font-semibold mb-0">Order Terbaru</h6>
                <a href="/orders" class="text-primary-600 dark:text-primary-600 hover-text-primary flex items-center gap-1">
                    Lihat Semua
                    <iconify-icon icon="solar:alt-arrow-right-linear" class="icon"></iconify-icon>
                </a>
            </div>
            <div class="card-body p-6">
                <div class="table-responsive scroll-sm">
                    <table class="table bordered-table style-two mb-0">
                        <thead>
                            <tr>
                                <th>Kode Order</th>
                                <th>Client</th>
                                <th>Layanan</th>
                                <th>Teknisi</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-primary-600">#ACD-0001</td>
                                <td>Budi Santoso</td>
                                <td>Cuci AC Split</td>
                                <td>Ahmad (Medium)</td>
                                <td>Rp 150.000</td>
                                <td><span class="bg-warning-100 dark:bg-warning-600/25 text-warning-600 px-3 py-1 rounded-full text-sm font-medium">Assigned</span></td>
                            </tr>
                            <tr>
                                <td class="text-primary-600">#ACD-0002</td>
                                <td>Siti Rahayu</td>
                                <td>Freon AC</td>
                                <td>Dani (Beginner)</td>
                                <td>Rp 250.000</td>
                                <td><span class="bg-info-100 dark:bg-info-600/25 text-info-600 px-3 py-1 rounded-full text-sm font-medium">On the way</span></td>
                            </tr>
                            <tr>
                                <td class="text-primary-600">#ACD-0003</td>
                                <td>Rudi Hermawan</td>
                                <td>Pasang AC Baru</td>
                                <td>Bima (Pro)</td>
                                <td>Rp 500.000</td>
                                <td><span class="bg-success-100 dark:bg-success-600/25 text-success-600 px-3 py-1 rounded-full text-sm font-medium">Selesai</span></td>
                            </tr>
                            <tr>
                                <td class="text-primary-600">#ACD-0004</td>
                                <td>Dewi Lestari</td>
                                <td>Cuci AC Split</td>
                                <td>-</td>
                                <td>Rp 150.000</td>
                                <td><span class="bg-neutral-100 dark:bg-neutral-600/25 text-neutral-600 px-3 py-1 rounded-full text-sm font-medium">Pending</span></td>
                            </tr>
                            <tr>
                                <td class="text-primary-600">#ACD-0005</td>
                                <td>Eko Prasetyo</td>
                                <td>Perbaikan AC</td>
                                <td>Ahmad (Medium)</td>
                                <td>Rp 350.000</td>
                                <td><span class="bg-danger-100 dark:bg-danger-600/25 text-danger-600 px-3 py-1 rounded-full text-sm font-medium">Dibatalkan</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Withdrawal Pending --}}
    <div class="lg:col-span-12 2xl:col-span-4">
        <div class="card border-0 overflow-hidden">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6 flex items-center justify-between">
                <h6 class="text-lg font-semibold mb-0">Withdrawal Pending</h6>
                <a href="/withdrawals" class="text-primary-600 hover-text-primary flex items-center gap-1">
                    Lihat Semua
                    <iconify-icon icon="solar:alt-arrow-right-linear" class="icon"></iconify-icon>
                </a>
            </div>
            <div class="card-body p-6">
                <div class="flex flex-col gap-5">

                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('assets/images/users/user1.png') }}" alt="" class="w-10 h-10 rounded-full shrink-0">
                            <div>
                                <h6 class="text-sm font-semibold mb-0">Ahmad Fauzi</h6>
                                <span class="text-xs text-secondary-light">Teknisi Medium · Makassar</span>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="text-sm font-semibold">Rp 320.000</span>
                            <div class="flex gap-1 mt-1 justify-end">
                                <a href="#" class="w-7 h-7 bg-success-100 text-success-600 rounded-full flex items-center justify-center text-xs">
                                    <iconify-icon icon="lucide:check"></iconify-icon>
                                </a>
                                <a href="#" class="w-7 h-7 bg-danger-100 text-danger-600 rounded-full flex items-center justify-center text-xs">
                                    <iconify-icon icon="lucide:x"></iconify-icon>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('assets/images/users/user2.png') }}" alt="" class="w-10 h-10 rounded-full shrink-0">
                            <div>
                                <h6 class="text-sm font-semibold mb-0">Dani Setiawan</h6>
                                <span class="text-xs text-secondary-light">Teknisi Beginner · Makassar</span>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="text-sm font-semibold">Rp 180.000</span>
                            <div class="flex gap-1 mt-1 justify-end">
                                <a href="#" class="w-7 h-7 bg-success-100 text-success-600 rounded-full flex items-center justify-center text-xs">
                                    <iconify-icon icon="lucide:check"></iconify-icon>
                                </a>
                                <a href="#" class="w-7 h-7 bg-danger-100 text-danger-600 rounded-full flex items-center justify-center text-xs">
                                    <iconify-icon icon="lucide:x"></iconify-icon>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('assets/images/users/user3.png') }}" alt="" class="w-10 h-10 rounded-full shrink-0">
                            <div>
                                <h6 class="text-sm font-semibold mb-0">Bima Prakoso</h6>
                                <span class="text-xs text-secondary-light">Teknisi Pro · Makassar</span>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="text-sm font-semibold">Rp 560.000</span>
                            <div class="flex gap-1 mt-1 justify-end">
                                <a href="#" class="w-7 h-7 bg-success-100 text-success-600 rounded-full flex items-center justify-center text-xs">
                                    <iconify-icon icon="lucide:check"></iconify-icon>
                                </a>
                                <a href="#" class="w-7 h-7 bg-danger-100 text-danger-600 rounded-full flex items-center justify-center text-xs">
                                    <iconify-icon icon="lucide:x"></iconify-icon>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/js/homeTwoChart.js') }}"></script>
@endpush