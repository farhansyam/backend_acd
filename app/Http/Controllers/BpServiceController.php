<?php

namespace App\Http\Controllers;

use App\Models\BpService;
use App\Models\BusinessPartner;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BpServiceController extends Controller
{
    private function getBusinessPartner()
    {
        if (Auth::user()->role === 'business_partner') {
            return BusinessPartner::where('user_id', Auth::id())->firstOrFail();
        }
        return null;
    }

    public function index()
    {
        $bp = $this->getBusinessPartner();

        $query = BpService::with(['businessPartner', 'serviceType'])->latest();

        if ($bp) {
            $query->where('bp_id', $bp->id);
        }

        $bpServices = $query->paginate(10);
        $businessPartners = Auth::user()->role === 'adminsuper'
            ? BusinessPartner::all()
            : null;

        return view('bp-services.index', compact('bpServices', 'businessPartners', 'bp'));
    }

    public function create()
    {
        $bp = $this->getBusinessPartner();
        $serviceTypes = ServiceType::where('is_active', 1)->get();
        $businessPartners = Auth::user()->role === 'adminsuper'
            ? BusinessPartner::all()
            : null;

        return view('bp-services.create', compact('serviceTypes', 'businessPartners', 'bp'));
    }

    public function store(Request $request)
    {
        $bp = $this->getBusinessPartner();

        $request->validate([
            'bp_id'           => $bp ? 'nullable' : 'required|exists:business_partners,id',
            'service_type_id' => 'required|exists:service_types,id',
            'base_service'    => 'required|numeric|min:0',
            'discount'        => 'nullable|numeric|min:0|max:100',
            'is_active'       => 'nullable|boolean',
            'banner'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $bannerPath = null;
        if ($request->hasFile('banner')) {
            $bannerPath = $request->file('banner')->store('banners', 'public');
        }

        BpService::create([
            'bp_id'           => $bp ? $bp->id : $request->bp_id,
            'service_type_id' => $request->service_type_id,
            'base_service'    => $request->base_service,
            'discount'        => $request->discount ?? 0,
            'is_active'       => $request->is_active ? 1 : 0,
            'banner'          => $bannerPath,
        ]);

        return redirect()->route('bp-services.index')
            ->with('success', 'BP Service berhasil ditambahkan.');
    }

    public function show(BpService $bpService)
    {
        $this->authorizeAccess($bpService);
        $bpService->load(['businessPartner', 'serviceType']);

        return view('bp-services.show', compact('bpService'));
    }

    public function edit(BpService $bpService)
    {
        $this->authorizeAccess($bpService);
        $bp = $this->getBusinessPartner();
        $serviceTypes = ServiceType::where('is_active', 1)->get();
        $businessPartners = Auth::user()->role === 'adminsuper'
            ? BusinessPartner::all()
            : null;

        return view('bp-services.edit', compact('bpService', 'serviceTypes', 'businessPartners', 'bp'));
    }

    public function update(Request $request, BpService $bpService)
    {
        $this->authorizeAccess($bpService);
        $bp = $this->getBusinessPartner();

        $request->validate([
            'bp_id'           => $bp ? 'nullable' : 'required|exists:business_partners,id',
            'service_type_id' => 'required|exists:service_types,id',
            'base_service'    => 'required|numeric|min:0',
            'discount'        => 'nullable|numeric|min:0|max:100',
            'is_active'       => 'nullable|boolean',
            'banner'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $bannerPath = $bpService->banner;
        if ($request->hasFile('banner')) {
            if ($bpService->banner) {
                Storage::disk('public')->delete($bpService->banner);
            }
            $bannerPath = $request->file('banner')->store('banners', 'public');
        }

        $bpService->update([
            'bp_id'           => $bp ? $bp->id : $request->bp_id,
            'service_type_id' => $request->service_type_id,
            'base_service'    => $request->base_service,
            'discount'        => $request->discount ?? 0,
            'is_active'       => $request->is_active ? 1 : 0,
            'banner'          => $bannerPath,
        ]);

        return redirect()->route('bp-services.index')
            ->with('success', 'BP Service berhasil diupdate.');
    }

    public function destroy(BpService $bpService)
    {
        $this->authorizeAccess($bpService);

        if ($bpService->banner) {
            Storage::disk('public')->delete($bpService->banner);
        }

        $bpService->delete();

        return redirect()->route('bp-services.index')
            ->with('success', 'BP Service berhasil dihapus.');
    }

    private function authorizeAccess(BpService $bpService)
    {
        if (Auth::user()->role === 'business_partner') {
            $bp = $this->getBusinessPartner();
            if ($bpService->bp_id !== $bp->id) {
                abort(403);
            }
        }
    }
}
