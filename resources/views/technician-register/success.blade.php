<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Berhasil</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
<div class="max-w-md mx-auto text-center px-4">
    <div class="bg-white rounded-2xl shadow p-10">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-800 mb-2">Pendaftaran Terkirim!</h2>
        <p class="text-gray-500 text-sm mb-6">
            Data kamu sedang dalam proses verifikasi oleh Business Partner di areamu.
            Kamu akan mendapat notifikasi setelah diverifikasi dan bisa login ke aplikasi.
        </p>
        <a href="{{ route('technician.register') }}"
           class="text-blue-600 text-sm hover:underline">Daftar akun lain</a>
    </div>
</div>
</body>
</html>