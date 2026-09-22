<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperadminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('karsa.superadmin.email');
        $password = config('karsa.superadmin.password');

        if (blank($email) || blank($password)) {
            $this->command?->warn('SUPERADMIN_EMAIL / SUPERADMIN_PASSWORD belum diatur di .env, superadmin dilewati.');

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => config('karsa.superadmin.name', 'Admin KARSA'),
                'password' => Hash::make($password),
                'role' => UserRole::Superadmin,
                'email_verified_at' => now(),
                'trust_score' => 100,
            ]
        );
    }
}
