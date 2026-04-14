@extends('layouts.app')
@section('title', 'Akun Customer')
@section('page-title', 'Akun Customer')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">Akun Customer</li>
@endsection

@section('content')
<div class="card border-0 mt-6">
    <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6 flex items-center justify-between">
        <h6 class="text-lg font-semibold mb-0">Daftar Customer</h6>
        <span class="text-sm text-secondary-light">Total: {{ $customers->total() }} customer</span>
    </div>
    <div class="card-body p-6">
        <div class="table-responsive">
            <table class="table bordered-table style-two mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Total Order</th>
                        <th>Saldo DikariPay</th>
                        <th>Status</th>
                        <th>Daftar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="flex items-center gap-3">
                                @if($customer->avatar)
                                    <img src="{{ $customer->avatar }}" class="w-8 h-8 rounded-full object-cover">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center font-bold text-sm">
                                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                                    </div>
                                @endif
                                <span class="font-medium">{{ $customer->name }}</span>
                            </div>
                        </td>
                        <td>{{ $customer->email }}</td>
                        <td>
                            <span class="font-bold">{{ $customer->orders_count }}</span> order
                        </td>
                        <td>Rp {{ number_format($customer->balance, 0, ',', '.') }}</td>
                        <td>
                            @if($customer->is_active)
                                <span class="bg-success-100 text-success-600 px-2 py-1 rounded-full text-xs font-medium">Aktif</span>
                            @else
                                <span class="bg-danger-100 text-danger-600 px-2 py-1 rounded-full text-xs font-medium">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-sm text-secondary-light">
                            {{ $customer->created_at->format('d M Y') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-secondary-light py-6">Belum ada customer.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $customers->links() }}</div>
    </div>
</div>
@endsection