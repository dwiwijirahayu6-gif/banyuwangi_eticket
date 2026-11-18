<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class TenantProfileController extends Controller
{
    public function index(Request $request, Tenant $tenant): View
    {
        $user = $request->user();
        $destination = $tenant->destinations()->first();

        return view('tenant.profile', [
            'tenant' => $tenant,
            'user' => $user,
            'destination' => $destination,
        ]);
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $user = $request->user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
        ];

        if ($request->filled('password')) {
            $rules['current_password'] = ['required', 'current_password'];
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }

        $validated = $request->validate($rules, [
            'name.required' => 'Nama operator wajib diisi.',
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $user->name = $validated['name'];

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('tenant.profil', ['tenant' => $tenant->code])
            ->with('success', 'Pengaturan akun berhasil disimpan.');
    }
}
