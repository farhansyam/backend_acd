<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — {{ config('app.name') }}</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}" sizes="16x16">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/remixicon.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/apexcharts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/prism.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/file-upload.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/audioplayer.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>
<body class="dark:bg-neutral-800 bg-neutral-100 dark:text-white">

<section class="bg-white dark:bg-dark-2 flex flex-wrap min-h-[100vh]">

    {{-- Kiri: ilustrasi --}}
    <div class="lg:w-1/2 lg:block hidden">
        <div class="flex items-center flex-col h-full justify-center">
            <img src="{{ asset('assets/images/auth/auth-img.png') }}" alt="">
        </div>
    </div>

    {{-- Kanan: form --}}
    <div class="lg:w-1/2 py-8 px-6 flex flex-col justify-center">
        <div class="lg:max-w-[464px] mx-auto w-full">

            {{-- Logo & heading --}}
            <div>
                <a href="#" class="mb-2.5 max-w-[290px]">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="{{ config('app.name') }}">
                </a>
                <h4 class="mb-3">Masuk ke Akun Anda</h4>
                <p class="mb-8 text-secondary-light text-lg">Selamat datang! Silakan masukkan detail login Anda.</p>
            </div>

            {{-- Error messages --}}
            @if ($errors->any())
                <div class="mb-4 p-3 rounded-xl bg-danger-100 text-danger-600 text-sm">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Session status --}}
            @if (session('status'))
                <div class="mb-4 p-3 rounded-xl bg-success-100 text-success-600 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf

                {{-- Email --}}
                <div class="icon-field mb-4 relative">
                    <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                        <iconify-icon icon="mage:email"></iconify-icon>
                    </span>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 dark:bg-dark-2 rounded-xl @error('email') border-danger-600 @enderror"
                        placeholder="Email"
                        required
                        autofocus
                        autocomplete="email"
                    >
                </div>

                {{-- Password --}}
                <div class="relative mb-5">
                    <div class="icon-field">
                        <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                            <iconify-icon icon="solar:lock-password-outline"></iconify-icon>
                        </span>
                        <input
                            type="password"
                            name="password"
                            id="your-password"
                            class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 dark:bg-dark-2 rounded-xl @error('password') border-danger-600 @enderror"
                            placeholder="Password"
                            required
                            autocomplete="current-password"
                        >
                    </div>
                    <span
                        class="toggle-password ri-eye-line cursor-pointer absolute end-0 top-1/2 -translate-y-1/2 me-4 text-secondary-light"
                        data-toggle="#your-password">
                    </span>
                </div>

                {{-- Remember me + Forgot password --}}
                <div class="mt-7">
                    <div class="flex justify-between gap-2">
                        <div class="flex items-center">
                            <input
                                class="form-check-input border border-neutral-300"
                                type="checkbox"
                                name="remember"
                                id="remember"
                                {{ old('remember') ? 'checked' : '' }}
                            >
                            <label class="ps-2" for="remember">Ingat saya</label>
                        </div>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-primary-600 font-medium hover:underline">
                                Lupa Password?
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn btn-primary justify-center text-sm btn-sm px-3 py-4 w-full rounded-xl mt-8">
                    Masuk
                </button>

            </form>
        </div>
    </div>

</section>

<script src="{{ asset('assets/js/lib/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('assets/js/lib/iconify-icon.min.js') }}"></script>
<script src="{{ asset('assets/js/flowbite.min.js') }}"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
<script>
    function initializePasswordToggle(toggleSelector) {
        $(toggleSelector).on('click', function () {
            $(this).toggleClass("ri-eye-off-line");
            var input = $($(this).attr("data-toggle"));
            if (input.attr("type") === "password") {
                input.attr("type", "text");
            } else {
                input.attr("type", "password");
            }
        });
    }
    initializePasswordToggle('.toggle-password');
</script>

</body>
</html>