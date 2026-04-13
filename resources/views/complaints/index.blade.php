@extends('layouts.app')
@section('title', 'Komplain & Garansi')
@section('page-title', 'Komplain & Garansi')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">Komplain</li>
@endsection

@section('content')
<div class="card border-0 mt-6">
    <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6 flex items-center justify-between">
        <h6 class="text-lg font-semibold mb-0">Daftar Komplain & Garansi</h6>
        <span class="text-sm text-secondary-light">Total: {{ $complaints->total() }} komplain</span>
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
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Judul</th>
                        <th>Teknisi</th>
                        <th>Status</th>
                        <th>Masa Garansi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($complaints as $complaint)
                    @php
                        $statusMap = [
                            'open'             => ['label' => 'Menunggu', 'class' => 'bg-danger-100 text-danger-600'],
                            'in_review'        => ['label' => 'Ditinjau', 'class' => 'bg-warning-100 text-warning-600'],
                            'rework_assigned'  => ['label' => 'Teknisi Ditugaskan', 'class' => 'bg-info-100 text-info-600'],
                            'rework_completed' => ['label' => 'Rework Selesai', 'class' => 'bg-purple-100 text-purple-600'],
                            'closed'           => ['label' => 'Selesai', 'class' => 'bg-success-100 text-success-600'],
                        ];
                        $s = $statusMap[$complaint->status] ?? ['label' => $complaint->status, 'class' => 'bg-neutral-100 text-neutral-600'];
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <span class="font-medium">#{{ $complaint->order_id }}</span>
                        </td>
                        <td>{{ $complaint->user->name ?? '-' }}</td>
                        <td>
                            <p class="font-medium text-sm">{{ Str::limit($complaint->title, 40) }}</p>
                        </td>
                        <td>
                            <div>
                                <p class="text-sm font-medium">{{ $complaint->technician->user->name ?? '-' }}</p>
                                @if($complaint->reworkTechnician)
                                    <p class="text-xs text-info-600">
                                        Rework: {{ $complaint->reworkTechnician->user->name ?? '-' }}
                                    </p>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $s['class'] }}">
                                {{ $s['label'] }}
                            </span>
                        </td>
                        <td>
                            @if($complaint->warranty_expires_at)
                                @if(now()->lt($complaint->warranty_expires_at))
                                    <span class="text-success-600 text-sm font-medium">
                                        {{ $complaint->warranty_expires_at->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="text-danger-600 text-sm">Kadaluarsa</span>
                                @endif
                            @else
                                <span class="text-secondary-light text-sm">-</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('complaints.show', $complaint) }}"
                               class="w-8 h-8 bg-primary-100 text-primary-600 rounded flex items-center justify-center">
                                <iconify-icon icon="lucide:eye"></iconify-icon>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-secondary-light py-6">Belum ada komplain.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $complaints->links() }}
        </div>

    </div>
</div>
@endsection