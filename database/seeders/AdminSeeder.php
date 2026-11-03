<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrNew(['email' => env('ADMIN_EMAIL')]);

        $user->forceFill([
            'name'     => 'Bu Desy',
            'password' => Hash::make(env('ADMIN_PASSWORD')),
            'role'     => 'owner',
        ])->save();
    }
}