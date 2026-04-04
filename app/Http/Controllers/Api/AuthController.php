<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Login / Register via Google (dipanggil dari Flutter)
     * Body: { "google_token": "..." }
     */
    public function googleLogin(Request $request)
    {
        $request->validate([
            'google_token' => 'required|string',
        ]);

        // Verifikasi token ke Google
        $googleResponse = Http::get('https://www.googleapis.com/oauth2/v3/userinfo', [
            'access_token' => $request->google_token,
        ]);

        if (!$googleResponse->ok()) {
            return response()->json([
                'message' => 'Token Google tidak valid.',
            ], 401);
        }

        $googleUser = $googleResponse->json();

        // Pastikan email ada
        if (empty($googleUser['email'])) {
            return response()->json([
                'message' => 'Tidak dapat mengambil email dari akun Google.',
            ], 422);
        }

        // Cari atau buat user
        $user = User::updateOrCreate(
            ['email' => $googleUser['email']],
            [
                'name'                 => $googleUser['name'] ?? $googleUser['email'],
                'google_id'            => $googleUser['sub'],
                'google_token'         => $request->google_token,
                'avatar'               => $googleUser['picture'] ?? null,
                'email_verified_at'    => now(),
                'is_active'            => 1,
                'last_login_at'        => now(),
                'device_type'          => $request->header('X-Device-Type', 'mobile'),
                'password'             => bcrypt(Str::random(32)), // password random, tidak dipakai
            ]
        );

        // Simpan fcm_token kalau dikirim
        if ($request->filled('fcm_token')) {
            $user->update(['fcm_token' => $request->fcm_token]);
        }

        // Buat Sanctum token
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token'   => $token,
            'user'    => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'avatar' => $user->avatar,
                'role'   => $user->role,
                'balance' => (float) $user->balance,

            ],
        ]);
    }

    /**
     * Ambil data user yang sedang login
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'avatar' => $user->avatar,
                'role'   => $user->role,
                'balance' => (float) $user->balance,
            ],
        ]);
    }

    /**
     * Logout — hapus token aktif
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }
}
