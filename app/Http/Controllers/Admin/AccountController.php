<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function edit()
    {
        return view('admin.account.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'max:100', 'confirmed', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
        ], [
            'required'         => ':attribute wajib diisi.',
            'current_password' => 'Password saat ini salah.',
            'min'              => ':attribute minimal :min karakter.',
            'confirmed'        => 'Konfirmasi password baru tidak sama.',
            'regex'            => 'Password baru harus mengandung huruf dan angka.',
        ], [
            'current_password' => 'Password saat ini',
            'password'         => 'Password baru',
        ]);

        $user = auth()->user();
        $user->password = Hash::make($data['password']);
        $user->save();

        return redirect()->route('admin.akun.edit')->with('status', 'Password berhasil diganti.');
    }
}