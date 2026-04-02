<?php

namespace App\Http\Controllers;

use App\Models\BusinessPartner;
use App\Models\Technician;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class TechnicianRegisterController extends Controller
{
    public function showForm()
    {
        return view('technician-register.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|string|min:8|confirmed',
            'phone'        => 'required|string|max:20',
            'province'     => 'required|string',
            'city'         => 'required|string',
            'districts'    => 'required|array|min:1',
            'districts.*'  => 'string',
            'address'      => 'nullable|string',
            'skck_file'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'ktp_photo'    => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'selfie_photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'certificate'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        // Cari BP terdekat berdasarkan provinsi dulu, kalau tidak ada cari by kota
        $bp = BusinessPartner::where('provience', $request->province)
            ->where('city', $request->city)
            ->first();

        // Kalau tidak ada BP di kota yang sama, cari by provinsi saja
        if (!$bp) {
            $bp = BusinessPartner::where('provience', $request->province)->first();
        }

        DB::transaction(function () use ($request, $bp) {
            // Simpan file dokumen
            $skckPath    = $request->hasFile('skck_file')    ? $request->file('skck_file')->store('technicians/skck', 'public')       : null;
            $ktpPath     = $request->hasFile('ktp_photo')    ? $request->file('ktp_photo')->store('technicians/ktp', 'public')        : null;
            $selfiePath  = $request->hasFile('selfie_photo') ? $request->file('selfie_photo')->store('technicians/selfie', 'public')  : null;
            $certPath    = $request->hasFile('certificate')  ? $request->file('certificate')->store('technicians/certificate', 'public') : null;

            // Buat user
            $user = User::create([
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'role'      => 'teknisi',
                'is_active' => 0, // nonaktif dulu sampai di-approve
            ]);

            // Buat record teknisi
            Technician::create([
                'user_id'      => $user->id,
                'bp_id'        => $bp?->id,
                'province'     => $request->province,
                'city'         => $request->city,
                'districts'    => $request->districts,
                'address'      => $request->address,
                'skck_file'    => $skckPath,
                'ktp_photo'    => $ktpPath,
                'selfie_photo' => $selfiePath,
                'certificate'  => $certPath,
                'status'       => 'pending',
            ]);
        });

        return redirect()->route('technician.register.success');
    }

    public function success()
    {
        return view('technician-register.success');
    }
}
