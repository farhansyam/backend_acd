@extends('layouts.app')

@section('title', 'Daftar Orders')
@section('page-title', 'Orders')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">Orders</li>
@endsection

{{-- CSS tambahan khusus halaman ini (opsional) --}}
@push('styles')
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/custom-order.css') }}"> --}}
@endpush

@section('content')
    <div class="grid grid-cols-12">
        <div class="col-span-12">
            <div class="card border-0 overflow-hidden">
                <div class="card-header">
                    <h6 class="card-title mb-0 text-lg">Daftar Order</h6>
                </div>
                <div class="card-body">
                    <table id="orders-table" class="border border-neutral-200 dark:border-neutral-600 rounded-lg border-separate">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Order</th>
                                <th>Client</th>
                                <th>Layanan</th>
                                <th>Teknisi</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                         
                        </tbody>
                    </table>

                   
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- JS tambahan khusus halaman ini (opsional) --}}
@push('scripts')
<script>
    if (document.getElementById("orders-table") && typeof simpleDatatables.DataTable !== 'undefined') {
        new simpleDatatables.DataTable("#orders-table", {
            columns: [{ select: [7], sortable: false }]
        });
    }
</script>
@endpush