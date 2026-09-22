<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (blank(config('services.google.client_id'))) {
            return redirect()->route('login')->with('status', 'Login dengan Google belum diaktifkan oleh admin.');
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        if (blank(config('services.google.client_id'))) {
            return redirect()->route('login');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()->route('login')->with('status', 'Gagal masuk dengan Google. Silakan coba lagi.');
        }

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if (! $user) {
            $user = User::create([
                'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'Pengguna KARSA',
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password' => null,
                'role' => UserRole::Warga,
                'email_verified_at' => now(),
            ]);

            Auth::login($user);

            // Akun baru dari Google belum memilih kabupaten/kota (CLAUDE.md §6 /daftar).
            return redirect()->route('profil')->with('status', 'Lengkapi kabupaten/kota Anda untuk melanjutkan.');
        }

        if ($user->is_blocked) {
            return redirect()->route('login')->with('status', 'Akun Anda diblokir. Hubungi admin untuk informasi lebih lanjut.');
        }

        if (blank($user->google_id)) {
            $user->update(['google_id' => $googleUser->getId()]);
        }

        Auth::login($user);

        return redirect()->route('menu');
    }
}
