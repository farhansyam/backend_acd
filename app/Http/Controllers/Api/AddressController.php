<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    // ─── GET semua alamat user ────────────────────────────────
    public function index(Request $request)
    {
        $addresses = $request->user()
            ->addresses()
            ->orderByDesc('is_primary')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['addresses' => $addresses]);
    }

    // ─── POST tambah alamat baru ──────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_type' => 'required|in:rumah,kantor,apartemen',
            'label'         => 'required|string|max:100',
            'province_id'   => 'required|string',
            'province_name' => 'required|string',
            'city_id'       => 'required|string',
            'city_name'     => 'required|string',
            'district_id'   => 'required|string',
            'district_name' => 'required|string',
            'village_id'    => 'nullable|string',
            'village_name'  => 'nullable|string',
            'full_address'  => 'required|string',
            'latitude'      => 'nullable|numeric|between:-90,90',
            'longitude'     => 'nullable|numeric|between:-180,180',
            'is_primary'    => 'boolean',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            // Kalau set sebagai primary, unset yang lain dulu
            if (!empty($validated['is_primary'])) {
                $request->user()->addresses()->update(['is_primary' => false]);
            }

            // Kalau ini alamat pertama, otomatis jadi primary
            $isFirst = $request->user()->addresses()->count() === 0;

            $address = $request->user()->addresses()->create([
                ...$validated,
                'is_primary' => $isFirst || (!empty($validated['is_primary'])),
            ]);

            return response()->json([
                'message' => 'Alamat berhasil ditambahkan.',
                'address' => $address,
            ], 201);
        });
    }

    // ─── GET detail satu alamat ───────────────────────────────
    public function show(Request $request, Address $address)
    {
        $this->authorizeAddress($request, $address);

        return response()->json(['address' => $address]);
    }

    // ─── PUT update alamat ────────────────────────────────────
    public function update(Request $request, Address $address)
    {
        $this->authorizeAddress($request, $address);

        $validated = $request->validate([
            'property_type' => 'sometimes|in:rumah,kantor,apartemen',
            'label'         => 'sometimes|string|max:100',
            'province_id'   => 'sometimes|string',
            'province_name' => 'sometimes|string',
            'city_id'       => 'sometimes|string',
            'city_name'     => 'sometimes|string',
            'district_id'   => 'sometimes|string',
            'district_name' => 'sometimes|string',
            'village_id'    => 'nullable|string',
            'village_name'  => 'nullable|string',
            'full_address'  => 'sometimes|string',
            'latitude'      => 'nullable|numeric|between:-90,90',
            'longitude'     => 'nullable|numeric|between:-180,180',
            'is_primary'    => 'boolean',
        ]);

        return DB::transaction(function () use ($request, $address, $validated) {
            if (!empty($validated['is_primary'])) {
                $request->user()->addresses()->update(['is_primary' => false]);
            }

            $address->update($validated);

            return response()->json([
                'message' => 'Alamat berhasil diperbarui.',
                'address' => $address->fresh(),
            ]);
        });
    }

    // ─── DELETE hapus alamat ──────────────────────────────────
    public function destroy(Request $request, Address $address)
    {
        $this->authorizeAddress($request, $address);

        $wasPrimary = $address->is_primary;
        $address->delete();

        // Kalau yang dihapus adalah primary, set primary ke alamat pertama
        if ($wasPrimary) {
            $first = $request->user()->addresses()->first();
            $first?->update(['is_primary' => true]);
        }

        return response()->json(['message' => 'Alamat berhasil dihapus.']);
    }

    // ─── PATCH set sebagai alamat utama ──────────────────────
    public function setPrimary(Request $request, Address $address)
    {
        $this->authorizeAddress($request, $address);

        DB::transaction(function () use ($request, $address) {
            $request->user()->addresses()->update(['is_primary' => false]);
            $address->update(['is_primary' => true]);
        });

        return response()->json([
            'message' => 'Alamat utama berhasil diubah.',
            'address' => $address->fresh(),
        ]);
    }

    // ─── Helper: pastikan alamat milik user ──────────────────
    private function authorizeAddress(Request $request, Address $address): void
    {
        abort_if(
            $address->user_id !== $request->user()->id,
            403,
            'Anda tidak memiliki akses ke alamat ini.'
        );
    }
}
