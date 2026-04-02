@extends('layouts.app')
@section('title', 'Teknisi Lokal')
@section('page-title', 'Teknisi Lokal')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">Teknisi Lokal</li>
@endsection

@section('content')
<div class="card border-0 mt-6">
    <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6 flex items-center justify-between">
        <h6 class="text-lg font-semibold mb-0">Daftar Teknisi Aktif</h6>
        <a href="{{ route('bp-technicians.approval') }}"
           class="btn btn-warning-600 flex items-center gap-2">
            <iconify-icon icon="lucide:clock"></iconify-icon>
            Approval Pending
        </a>
    </div>
    <div class="card-body p-6">

        @if(session('success'))
            <div class="bg-success-100 text-success-600 px-4 py-3 rounded mb-4 flex items-center gap-2">
                <iconify-icon icon="lucide:check-circle"></iconify-icon> {{ session('success') }}
            </div>
        @endif

        <div class="table-responsive">
            <table class="table bordered-table style-two mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Kota</th>
                        <th>Grade</th>
                        <th>Bergabung</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($technicians as $tech)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="font-medium">{{ $tech->user->name }}</td>
                        <td>{{ $tech->user->email }}</td>
                        <td>{{ $tech->city ?? '-' }}</td>
                        <td>
                            @php
                                $gradeColor = match($tech->grade) {
                                    'beginner' => 'bg-info-100 text-info-600',
                                    'medium'   => 'bg-warning-100 text-warning-600',
                                    'pro'      => 'bg-success-100 text-success-600',
                                    default    => 'bg-neutral-100 text-neutral-600',
                                };
                            @endphp
                            <span class="{{ $gradeColor }} px-3 py-1 rounded-full text-sm font-medium capitalize">
                                {{ $tech->grade ?? '-' }}
                            </span>
                        </td>
                        <td>{{ $tech->approved_at?->format('d M Y') ?? '-' }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('bp-technicians.show', $tech) }}"
                                   class="w-8 h-8 bg-info-100 text-info-600 rounded flex items-center justify-center">
                                    <iconify-icon icon="lucide:eye"></iconify-icon>
                                </a>
                                <form action="{{ route('bp-technicians.destroy', $tech) }}" method="POST"
                                      onsubmit="return confirm('Yakin nonaktifkan teknisi ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="w-8 h-8 bg-danger-100 text-danger-600 rounded flex items-center justify-center">
                                        <iconify-icon icon="lucide:user-x"></iconify-icon>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-secondary-light py-10">
                            <iconify-icon icon="lucide:inbox" class="text-4xl mb-2 block"></iconify-icon>
                            Belum ada teknisi aktif di area kamu.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $technicians->links() }}</div>
    </div>
</div>
@endsection