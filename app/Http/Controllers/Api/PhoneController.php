<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserPhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PhoneController extends Controller
{
    // ─── GET semua nomor user ─────────────────────────────────
    public function index(Request $request)
    {
        $phones = $request->user()
            ->phones()
            ->orderByDesc('is_primary')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['phones' => $phones]);
    }

    // ─── POST tambah nomor baru ───────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label'        => 'required|string|max:50',
            'phone_number' => 'required|string|max:20',
            'is_primary'   => 'boolean',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            if (!empty($validated['is_primary'])) {
                $request->user()->phones()->update(['is_primary' => false]);
            }

            $isFirst = $request->user()->phones()->count() === 0;

            $phone = $request->user()->phones()->create([
                ...$validated,
                'is_primary' => $isFirst || (!empty($validated['is_primary'])),
            ]);

            return response()->json([
                'message' => 'Nomor berhasil ditambahkan.',
                'phone'   => $phone,
            ], 201);
        });
    }

    // ─── PUT update nomor ─────────────────────────────────────
    public function update(Request $request, UserPhone $phone)
    {
        $this->authorizePhone($request, $phone);

        $validated = $request->validate([
            'label'        => 'sometimes|string|max:50',
            'phone_number' => 'sometimes|string|max:20',
            'is_primary'   => 'boolean',
        ]);

        return DB::transaction(function () use ($request, $phone, $validated) {
            if (!empty($validated['is_primary'])) {
                $request->user()->phones()->update(['is_primary' => false]);
            }

            $phone->update($validated);

            return response()->json([
                'message' => 'Nomor berhasil diperbarui.',
                'phone'   => $phone->fresh(),
            ]);
        });
    }

    // ─── DELETE hapus nomor ───────────────────────────────────
    public function destroy(Request $request, UserPhone $phone)
    {
        $this->authorizePhone($request, $phone);

        $wasPrimary = $phone->is_primary;
        $phone->delete();

        if ($wasPrimary) {
            $first = $request->user()->phones()->first();
            $first?->update(['is_primary' => true]);
        }

        return response()->json(['message' => 'Nomor berhasil dihapus.']);
    }

    // ─── PATCH set sebagai nomor utama ────────────────────────
    public function setPrimary(Request $request, UserPhone $phone)
    {
        $this->authorizePhone($request, $phone);

        DB::transaction(function () use ($request, $phone) {
            $request->user()->phones()->update(['is_primary' => false]);
            $phone->update(['is_primary' => true]);
        });

        return response()->json([
            'message' => 'Nomor utama berhasil diubah.',
            'phone'   => $phone->fresh(),
        ]);
    }

    // ─── Helper ───────────────────────────────────────────────
    private function authorizePhone(Request $request, UserPhone $phone): void
    {
        abort_if(
            $phone->user_id !== $request->user()->id,
            403,
            'Anda tidak memiliki akses ke nomor ini.'
        );
    }
}
