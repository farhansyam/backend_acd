<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}" sizes="16x16">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
    @stack('styles')
</head>
<body class="dark:bg-neutral-800 bg-neutral-100 dark:text-white">

    @include('layouts.partials.sidebar')

    <main class="dashboard-main">
        @include('layouts.partials.header')

        <div class="dashboard-main-body">
            {{-- Breadcrumb --}}
            <div class="flex flex-wrap items-center justify-between gap-2 mb-6">
                <h6 class="font-semibold mb-0 dark:text-white">@yield('page-title')</h6>
                <ul class="flex items-center gap-[6px]">
                    <li class="font-medium">
                        <a href="/dashboard" class="flex items-center gap-2 hover:text-primary-600 dark:text-white">
                            <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                            Dashboard
                        </a>
                    </li>
                    @yield('breadcrumb')
                </ul>
            </div>

            @yield('content')
        </div>

        @include('layouts.partials.footer')
    </main>

    <script src="{{ asset('assets/js/lib/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/simple-datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/iconify-icon.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/magnifc-popup.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/slick.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/prism.js') }}"></script>
    <script src="{{ asset('assets/js/lib/file-upload.js') }}"></script>
    <script src="{{ asset('assets/js/lib/audioplayer.js') }}"></script>
    <script src="{{ asset('assets/js/flowbite.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    @stack('scripts')

</body>
</html>