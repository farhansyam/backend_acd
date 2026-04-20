@extends('layouts.app')
@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan Aplikasi')

@section('breadcrumb')
    <li class="dark:text-white">-</li>
    <li class="font-medium dark:text-white">Pengaturan</li>
@endsection

@section('content')

@if(session('success'))
    <div class="bg-success-100 text-success-600 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
        <iconify-icon icon="lucide:check-circle"></iconify-icon>
        {{ session('success') }}
    </div>
@endif

<form action="{{ route('settings.update') }}" method="POST">
    @csrf @method('PUT')

    <div class="grid grid-cols-12 gap-6 mt-6">

        {{-- WhatsApp --}}
        <div class="col-span-12 lg:col-span-6">
            <div class="card border-0">
                <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-4 px-6">
                    <h6 class="font-semibold mb-0 flex items-center gap-2">
                        <iconify-icon icon="lucide:message-circle" class="text-success-600"></iconify-icon>
                        Kontak WhatsApp
                    </h6>
                </div>
                <div class="card-body p-6 space-y-4">
                    @foreach($settings as $i => $setting)
                    <input type="hidden" name="settings[{{ $i }}][key]" value="{{ $setting->key }}">
                    @if(in_array($setting->key, ['wa_ac_industri', 'wa_cs']))
                    <div>
                        <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                            {{ $setting->label }}
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="text-secondary-light text-sm px-3 py-2 bg-neutral-100 rounded-lg">+</span>
                            <input type="text" name="settings[{{ $i }}][value]"
                                value="{{ $setting->value }}"
                                class="form-control radius-8"
                                placeholder="628xxxxxxxxxx">
                        </div>
                        <p class="text-xs text-secondary-light mt-1">Format: 628xxx (tanpa tanda +)</p>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Pesan WA --}}
        <div class="col-span-12 lg:col-span-6">
            <div class="card border-0">
                <div class="card-header border-b border-neutral-200 dark:border-neutral-600 py-4 px-6">
                    <h6 class="font-semibold mb-0 flex items-center gap-2">
                        <iconify-icon icon="lucide:message-square" class="text-primary-600"></iconify-icon>
                        Pesan Default WhatsApp
                    </h6>
                </div>
                <div class="card-body p-6 space-y-4">
                    @foreach($settings as $i => $setting)
                    @if(in_array($setting->key, ['wa_message_ac_industri', 'wa_message_cs']))
                    <div>
                        <label class="form-label fw-semibold text-primary-light text-sm mb-2">
                            {{ $setting->label }}
                        </label>
                        <textarea name="settings[{{ $i }}][value]" rows="3"
                            class="form-control radius-8"
                            placeholder="Pesan default...">{{ $setting->value }}</textarea>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    <div class="mt-6">
        <button type="submit" class="btn btn-primary-600 flex items-center gap-2">
            <iconify-icon icon="lucide:save"></iconify-icon>
            Simpan Pengaturan
        </button>
    </div>

</form>
@endsection