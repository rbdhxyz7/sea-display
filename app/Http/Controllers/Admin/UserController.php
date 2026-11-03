<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private const MESSAGES = [
        'required'  => ':attribute wajib diisi.',
        'email'     => 'Format email tidak valid.',
        'unique'    => ':attribute sudah dipakai akun lain.',
        'max'       => ':attribute terlalu panjang.',
        'min'       => ':attribute minimal :min karakter.',
        'confirmed' => 'Konfirmasi password tidak sama.',
        'regex'     => 'Password harus mengandung huruf dan angka.',
        'in'        => 'Peran tidak valid.',
    ];

    private const ATTRIBUTES = [
        'name'     => 'Nama',
        'email'    => 'Email',
        'password' => 'Password',
        'role'     => 'Peran',
    ];

    public function index()
    {
        $users = User::orderByRaw("CASE WHEN role = 'owner' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.form', ['user' => new User(['role' => 'staff'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->normalizeEmail($request);

        $data = $request->validate($this->rules(null, false), self::MESSAGES, self::ATTRIBUTES);

        $user = new User();
        $user->forceFill([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => $data['role'],
            'is_active' => true,
        ])->save();

        return redirect()->route('admin.pengguna.index')
            ->with('status', "Akun {$user->name} berhasil dibuat.");
    }

    public function edit(User $user)
    {
        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->normalizeEmail($request);

        $isSelf = $user->id === auth()->id();
        $data = $request->validate($this->rules($user, $isSelf), self::MESSAGES, self::ATTRIBUTES);

        $user->forceFill([
            'name'  => $data['name'],
            'email' => $data['email'],
        ]);

        // Peran dan status akun sendiri tidak bisa diubah, supaya selalu ada pemilik aktif
        if (! $isSelf) {
            $user->role = $data['role'];
            $user->is_active = $request->boolean('is_active');
        }

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('admin.pengguna.index')
            ->with('status', "Akun {$user->name} berhasil diperbarui.");
    }

    private function rules(?User $user, bool $isSelf): array
    {
        return [
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user?->id)],
            'role'     => [$isSelf ? 'nullable' : 'required', Rule::in(['owner', 'staff'])],
            'password' => [
                $user ? 'nullable' : 'required',
                'string', 'min:8', 'max:100', 'confirmed',
                'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/',
            ],
        ];
    }

    private function normalizeEmail(Request $request): void
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);
    }
}