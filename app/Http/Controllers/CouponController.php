<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::withCount('usages')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('coupons.form', ['coupon' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code'               => 'required|string|max:50|unique:coupons,code',
            'name'               => 'required|string|max:100',
            'discount_percent'   => 'required|numeric|min:1|max:100',
            'max_discount'       => 'nullable|numeric|min:0',
            'min_order'          => 'required|numeric|min:0',
            'valid_from'         => 'required|date',
            'valid_until'        => 'required|date|after_or_equal:valid_from',
            'max_usage_per_user' => 'required|integer|min:1',
            'all_services'       => 'boolean',
            'is_active'          => 'boolean',
        ]);

        Coupon::create([
            'code'               => strtoupper($request->code),
            'name'               => $request->name,
            'discount_percent'   => $request->discount_percent,
            'max_discount'       => $request->max_discount,
            'min_order'          => $request->min_order,
            'valid_from'         => $request->valid_from,
            'valid_until'        => $request->valid_until,
            'max_usage_per_user' => $request->max_usage_per_user,
            'all_services'       => $request->boolean('all_services'),
            'is_active'          => $request->boolean('is_active', true),
        ]);

        return redirect()->route('coupons.index')
            ->with('success', 'Kupon berhasil dibuat.');
    }

    public function edit(Coupon $coupon)
    {
        return view('coupons.form', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'code'               => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'name'               => 'required|string|max:100',
            'discount_percent'   => 'required|numeric|min:1|max:100',
            'max_discount'       => 'nullable|numeric|min:0',
            'min_order'          => 'required|numeric|min:0',
            'valid_from'         => 'required|date',
            'valid_until'        => 'required|date|after_or_equal:valid_from',
            'max_usage_per_user' => 'required|integer|min:1',
        ]);

        $coupon->update([
            'code'               => strtoupper($request->code),
            'name'               => $request->name,
            'discount_percent'   => $request->discount_percent,
            'max_discount'       => $request->max_discount,
            'min_order'          => $request->min_order,
            'valid_from'         => $request->valid_from,
            'valid_until'        => $request->valid_until,
            'max_usage_per_user' => $request->max_usage_per_user,
            'all_services'       => $request->boolean('all_services'),
            'is_active'          => $request->boolean('is_active'),
        ]);

        return redirect()->route('coupons.index')
            ->with('success', 'Kupon berhasil diperbarui.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->route('coupons.index')
            ->with('success', 'Kupon berhasil dihapus.');
    }

    public function toggleActive(Coupon $coupon)
    {
        $coupon->update(['is_active' => !$coupon->is_active]);
        return back()->with('success', 'Status kupon diperbarui.');
    }

    // Generate kode kupon random
    public function generateCode()
    {
        $code = strtoupper(Str::random(8));
        return response()->json(['code' => $code]);
    }
}
