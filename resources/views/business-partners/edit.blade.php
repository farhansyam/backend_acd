@extends('layouts.app')
@section('title', 'Edit Business Partner')
@section('page-title', 'Edit Business Partner')

@section('breadcrumb')
    <li><a href="{{ route('business-partners.index') }}" class="dark:text-white">Business Partner</a></li>
    <li class="font-medium dark:text-white">Edit</li>
@endsection

@section('content')
<div class="card border-0 mt-6">
    <div class="card-header border-b border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-700 py-4 px-6">
        <h6 class="text-lg font-semibold mb-0">Edit Business Partner</h6>
    </div>
    <div class="card-body p-6">

        @if($errors->any())
            <div class="bg-danger-100 text-danger-600 px-4 py-3 rounded mb-4">
                <ul class="mb-0 list-disc pl-4">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('business-partners.update', $businessPartner) }}" method="POST">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <label class="form-label font-medium text-sm">Nama <span class="text-danger-600">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $businessPartner->name) }}"
                           class="form-control">
                </div>

                <div>
                    <label class="form-label font-medium text-sm">Email <span class="text-danger-600">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $businessPartner->user->email) }}"
                           class="form-control">
                </div>

                <div>
                    <label class="form-label font-medium text-sm">Password Baru <span class="text-secondary-light text-xs">(kosongkan jika tidak diubah)</span></label>
                    <input type="password" name="password" class="form-control" placeholder="Min. 8 karakter">
                </div>

                <div>
                    <label class="form-label font-medium text-sm">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>

                <div>
                    <label class="form-label font-medium text-sm">Kota</label>
                    <input type="text" name="city" value="{{ old('city', $businessPartner->city) }}"
                           class="form-control">
                </div>

                <div>
                    <label class="form-label font-medium text-sm">Provinsi</label>
                    <input type="text" name="province" value="{{ old('province', $businessPartner->provience) }}"
                           class="form-control">
                </div>

                <div class="md:col-span-2">
                    <label class="form-label font-medium text-sm">Alamat</label>
                    <textarea name="address" rows="3" class="form-control">{{ old('address', $businessPartner->address) }}</textarea>
                </div>

                <div>
                    <label class="form-label font-medium text-sm">Balance</label>
                    <input type="number" name="balance" value="{{ old('balance', $businessPartner->balance) }}"
                           class="form-control">
                </div>

            </div>

            <div class="flex items-center gap-3 mt-6">
                <button type="submit" class="btn btn-primary-600">Update</button>
                <a href="{{ route('business-partners.index') }}" class="btn btn-neutral-200">Batal</a>
            </div>
        </form>

    </div>
</div>
@endsection