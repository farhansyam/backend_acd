@extends('layouts.app')
@section('title', 'Akun Teknisi')
@section('page-title', 'Akun Teknisi')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">Akun Teknisi</li>
@endsection

@section('content')
<div class="card border-0 mt-6">
    <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6 flex items-center justify-between">
        <h6 class="text-lg font-semibold mb-0">Daftar Teknisi</h6>
        <span class="text-sm text-secondary-light">Total: {{ $technicians->total() }} teknisi</span>
    </div>
    <div class="card-body p-6">
        <div class="table-responsive">
            <table class="table bordered-table style-two mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Business Partner</th>
                        <th>Grade</th>
                        <th>Kota</th>
                        <th>Saldo</th>
                        <th>Status</th>
                        <th>Daftar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($technicians as $tech)
                    @php
                        $gradeMap = [
                            'beginner' => 'bg-info-100 text-info-600',
                            'medium'   => 'bg-warning-100 text-warning-600',
                            'pro'      => 'bg-success-100 text-success-600',
                        ];
                        $statusMap = [
                            'approved' => ['class' => 'bg-success-100 text-success-600', 'label' => 'Aktif'],
                            'pending'  => ['class' => 'bg-warning-100 text-warning-600', 'label' => 'Menunggu'],
                            'rejected' => ['class' => 'bg-danger-100 text-danger-600',  'label' => 'Ditolak'],
                        ];
                        $s = $statusMap[$tech->status] ?? ['class' => 'bg-neutral-100 text-neutral-600', 'label' => $tech->status];
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="font-medium">{{ $tech->user->name ?? '-' }}</td>
                        <td class="text-sm">{{ $tech->user->email ?? '-' }}</td>
                        <td>{{ $tech->businessPartner->name ?? '-' }}</td>
                        <td>
                            <span class="px-2 py-1 rounded-full text-xs font-medium capitalize {{ $gradeMap[$tech->grade] ?? 'bg-neutral-100 text-neutral-600' }}">
                                {{ $tech->grade }}
                            </span>
                        </td>
                        <td class="text-sm">{{ $tech->city ?? '-' }}</td>
                        <td class="font-bold">Rp {{ number_format($tech->balance, 0, ',', '.') }}</td>
                        <td>
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $s['class'] }}">
                                {{ $s['label'] }}
                            </span>
                        </td>
                        <td class="text-sm text-secondary-light">
                            {{ $tech->created_at->format('d M Y') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-secondary-light py-6">Belum ada teknisi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $technicians->links() }}</div>
    </div>
</div>
@endsection