<?php

namespace App\Actions\Fortify;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Registrasi menerima email ATAU nomor HP (CLAUDE.md §6 /daftar).
     * Kolom "identity" pada form berisi salah satunya; dideteksi otomatis
     * dari ada tidaknya karakter "@".
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        $identity = trim((string) ($input['identity'] ?? ''));
        $isEmail = str_contains($identity, '@');

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'identity' => ['required', 'string', 'max:255'],
            'regency_id' => ['required', 'exists:regencies,id'],
            'terms' => ['accepted'],
            'password' => $this->passwordRules(),
        ], [
            'terms.accepted' => 'Anda harus menyetujui Syarat & Kebijakan Privasi.',
        ])->validate();

        if ($isEmail) {
            Validator::make(['email' => $identity], [
                'email' => ['required', 'email', 'max:255', Rule::unique(User::class, 'email')],
            ])->validate();
        } else {
            Validator::make(['phone' => $identity], [
                'phone' => ['required', 'max:20', 'regex:/^[0-9+][0-9\-\s]{7,19}$/', Rule::unique(User::class, 'phone')],
            ])->validate();
        }

        return User::create([
            'name' => $input['name'],
            'email' => $isEmail ? $identity : null,
            'phone' => $isEmail ? null : $identity,
            'password' => Hash::make($input['password']),
            'role' => UserRole::Warga,
            'home_regency_id' => $input['regency_id'],
            'email_verified_at' => $isEmail ? null : now(),
        ]);
    }
}
