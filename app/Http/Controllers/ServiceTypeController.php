<?php

namespace App\Http\Controllers;

use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceTypeController extends Controller
{
    public function index()
    {
        $serviceTypes = ServiceType::latest()->paginate(10);
        return view('service-types.index', compact('serviceTypes'));
    }

    public function create()
    {
        return view('service-types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:service_types,name',
            'description' => 'nullable|string',
            'is_active'   => 'required|in:0,1',
        ]);

        ServiceType::create([
            'name'        => $request->name,
            'description' => $request->description,
            'is_active'   => $request->is_active,
        ]);

        return redirect()->route('service-types.index')
            ->with('success', 'Jenis layanan berhasil ditambahkan.');
    }

    public function show(ServiceType $serviceType)
    {
        $serviceType->load('bpServices.businessPartner');
        return view('service-types.show', compact('serviceType'));
    }

    public function edit(ServiceType $serviceType)
    {
        return view('service-types.edit', compact('serviceType'));
    }

    public function update(Request $request, ServiceType $serviceType)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:service_types,name,' . $serviceType->id,
            'description' => 'nullable|string',
            'is_active'   => 'required|in:0,1',
        ]);

        $serviceType->update([
            'name'        => $request->name,
            'description' => $request->description,
            'is_active'   => $request->is_active,
        ]);

        return redirect()->route('service-types.index')
            ->with('success', 'Jenis layanan berhasil diupdate.');
    }

    public function destroy(ServiceType $serviceType)
    {
        if ($serviceType->bpServices()->count() > 0) {
            return redirect()->route('service-types.index')
                ->with('error', 'Jenis layanan tidak bisa dihapus karena masih digunakan oleh BP Service.');
        }

        $serviceType->delete();

        return redirect()->route('service-types.index')
            ->with('success', 'Jenis layanan berhasil dihapus.');
    }
}
