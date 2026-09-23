<?php

use App\Models\Regency;
use App\Models\User;

it('mendaftarkan pengguna baru dengan email', function () {
    $regency = Regency::factory()->create();

    $response = $this->post('/register', [
        'name' => 'Warga Ciruas',
        'identity' => 'warga@example.com',
        'regency_id' => $regency->id,
        'terms' => '1',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect();
    $this->assertAuthenticated();

    $user = User::where('email', 'warga@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->phone)->toBeNull()
        ->and($user->role->value)->toBe('warga')
        ->and($user->home_regency_id)->toBe($regency->id);
});

it('mendaftarkan pengguna baru dengan nomor HP', function () {
    $regency = Regency::factory()->create();

    $response = $this->post('/register', [
        'name' => 'Warga Ciruas',
        'identity' => '081234567890',
        'regency_id' => $regency->id,
        'terms' => '1',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect();
    $this->assertAuthenticated();

    $user = User::where('phone', '081234567890')->first();
    expect($user)->not->toBeNull()
        ->and($user->email)->toBeNull()
        ->and($user->email_verified_at)->not->toBeNull(); // nomor HP dianggap terverifikasi otomatis
});

it('menolak registrasi tanpa menyetujui syarat', function () {
    $regency = Regency::factory()->create();

    $response = $this->post('/register', [
        'name' => 'Warga Ciruas',
        'identity' => 'warga2@example.com',
        'regency_id' => $regency->id,
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('terms');
    $this->assertGuest();
});

it('menolak registrasi dengan email yang sudah terdaftar', function () {
    $regency = Regency::factory()->create();
    User::factory()->create(['email' => 'ada@example.com', 'phone' => null]);

    $response = $this->post('/register', [
        'name' => 'Warga Lain',
        'identity' => 'ada@example.com',
        'regency_id' => $regency->id,
        'terms' => '1',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
});

it('menampilkan halaman pendaftaran tanpa tombol daftar google', function () {
    $response = $this->get('/daftar');

    $response->assertSuccessful();
    $response->assertDontSee('Daftar dengan Google');
    $response->assertDontSee('/auth/google');
});
