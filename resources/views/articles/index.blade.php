@extends('layouts.app')
@section('title', 'Promo & Tips')
@section('page-title', 'Promo & Tips')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">Promo & Tips</li>
@endsection

@section('content')
<div class="card border-0 mt-6">
    <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6 flex items-center justify-between">
        <h6 class="text-lg font-semibold mb-0">Daftar Promo & Tips</h6>
        <a href="{{ route('articles.create') }}" class="btn btn-primary-600 flex items-center gap-2">
            <iconify-icon icon="lucide:plus"></iconify-icon> Tambah
        </a>
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
                        <th>Judul</th>
                        <th>Tipe</th>
                        <th>Warna</th>
                        <th>Kadaluarsa</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($articles as $article)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="flex items-center gap-3">
                                @if($article->image)
                                    <img src="{{ $article->image_url }}" class="w-10 h-10 rounded-lg object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                                         style="background-color: {{ $article->color_hex }}20">
                                        <iconify-icon icon="{{ $article->type === 'promo' ? 'lucide:tag' : 'lucide:lightbulb' }}"
                                            style="color: {{ $article->color_hex }}"></iconify-icon>
                                    </div>
                                @endif
                                <div>
                                    <p class="font-medium text-sm">{{ $article->title }}</p>
                                    <p class="text-xs text-secondary-light">{{ $article->subtitle }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                {{ $article->type === 'promo' ? 'bg-warning-100 text-warning-600' : 'bg-info-100 text-info-600' }}">
                                {{ $article->getTypeLabel() }}
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full border border-neutral-200"
                                     style="background-color: {{ $article->color_hex }}"></div>
                                <span class="text-sm font-mono">{{ $article->color_hex }}</span>
                            </div>
                        </td>
                        <td class="text-sm text-secondary-light">
                            @if($article->expired_at)
                                @if($article->isExpired())
                                    <span class="text-danger-600 font-medium">Kadaluarsa</span>
                                @else
                                    {{ $article->expired_at->format('d M Y') }}
                                @endif
                            @else
                                <span class="text-secondary-light">-</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('articles.toggle', $article) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="px-2 py-1 rounded-full text-xs font-medium cursor-pointer
                                        {{ $article->is_active ? 'bg-success-100 text-success-600' : 'bg-neutral-100 text-neutral-500' }}">
                                    {{ $article->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </form>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('articles.edit', $article) }}"
                                   class="w-8 h-8 bg-warning-100 text-warning-600 rounded flex items-center justify-center">
                                    <iconify-icon icon="lucide:pencil"></iconify-icon>
                                </a>
                                <form action="{{ route('articles.destroy', $article) }}" method="POST"
                                      onsubmit="return confirm('Hapus artikel ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="w-8 h-8 bg-danger-100 text-danger-600 rounded flex items-center justify-center">
                                        <iconify-icon icon="lucide:trash-2"></iconify-icon>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-secondary-light py-6">Belum ada artikel.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $articles->links() }}</div>
    </div>
</div>
@endsection