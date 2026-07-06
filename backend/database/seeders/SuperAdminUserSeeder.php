<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'superadmin@pnknowledgecampus.edu'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('ChangeMe!12345'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        if (! $user->hasRole('Super Admin')) {
            $user->assignRole('Super Admin');
        }
    }
}
