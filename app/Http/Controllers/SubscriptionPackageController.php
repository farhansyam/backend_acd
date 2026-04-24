<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPackage;
use Illuminate\Http\Request;

class SubscriptionPackageController extends Controller
{
    public function index()
    {
        $packages = SubscriptionPackage::latest()->get();
        return view('subscription-packages.index', compact('packages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'type'             => 'required|in:hemat,rutin,intensif',
            'interval_months'  => 'required|integer|min:1',
            'total_sessions'   => 'required|integer|min:1',
            'price_multiplier' => 'required|numeric|min:0.1|max:1',
            'description'      => 'nullable|string|max:500',
            'is_active'        => 'required|in:0,1',
        ]);

        SubscriptionPackage::updateOrCreate(
            ['type' => $request->type],
            $request->only(['name', 'type', 'interval_months', 'total_sessions', 'price_multiplier', 'description', 'is_active'])
        );

        return redirect()->route('subscription-packages.index')
            ->with('success', 'Paket berhasil disimpan.');
    }

    public function update(Request $request, SubscriptionPackage $subscriptionPackage)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'interval_months'  => 'required|integer|min:1',
            'total_sessions'   => 'required|integer|min:1',
            'price_multiplier' => 'required|numeric|min:0.1|max:1',
            'description'      => 'nullable|string|max:500',
            'is_active'        => 'required|in:0,1',
        ]);

        $subscriptionPackage->update(
            $request->only(['name', 'interval_months', 'total_sessions', 'price_multiplier', 'description', 'is_active'])
        );

        return redirect()->route('subscription-packages.index')
            ->with('success', 'Paket berhasil diupdate.');
    }

    public function toggleActive(SubscriptionPackage $subscriptionPackage)
    {
        $subscriptionPackage->update(['is_active' => !$subscriptionPackage->is_active]);

        return redirect()->route('subscription-packages.index')
            ->with('success', 'Status paket berhasil diubah.');
    }
}
