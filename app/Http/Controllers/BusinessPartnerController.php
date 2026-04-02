<?php

namespace App\Http\Controllers;

use App\Models\BusinessPartner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BusinessPartnerController extends Controller
{
    public function index()
    {
        $businessPartners = BusinessPartner::with('user')
            ->latest()
            ->paginate(10);

        return view('business-partners.index', compact('businessPartners'));
    }

    public function create()
    {
        return view('business-partners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'city'     => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'address'  => 'nullable|string',
            'balance'  => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'business_partner',
                'is_active' => 1,
            ]);

            BusinessPartner::create([
                'user_id'  => $user->id,
                'name'     => $request->name,
                'city'     => $request->city,
                'provience' => $request->province,
                'address'  => $request->address,
                'balance'  => $request->balance ?? 0,
            ]);
        });

        return redirect()->route('business-partners.index')
            ->with('success', 'Business Partner berhasil ditambahkan.');
    }

    public function show(BusinessPartner $businessPartner)
    {
        $businessPartner->load(['user', 'bpServices.serviceType']);

        return view('business-partners.show', compact('businessPartner'));
    }

    public function edit(BusinessPartner $businessPartner)
    {
        $businessPartner->load('user');

        return view('business-partners.edit', compact('businessPartner'));
    }

    public function update(Request $request, BusinessPartner $businessPartner)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $businessPartner->user_id,
            'password' => 'nullable|string|min:8|confirmed',
            'city'     => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'address'  => 'nullable|string',
            'balance'  => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $businessPartner) {
            $userData = [
                'name'  => $request->name,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $businessPartner->user->update($userData);

            $businessPartner->update([
                'name'     => $request->name,
                'city'     => $request->city,
                'provience' => $request->province,
                'address'  => $request->address,
                'balance'  => $request->balance ?? 0,
            ]);
        });

        return redirect()->route('business-partners.index')
            ->with('success', 'Business Partner berhasil diupdate.');
    }

    public function destroy(BusinessPartner $businessPartner)
    {
        DB::transaction(function () use ($businessPartner) {
            $businessPartner->delete();
            $businessPartner->user->delete();
        });

        return redirect()->route('business-partners.index')
            ->with('success', 'Business Partner berhasil dihapus.');
    }
}
