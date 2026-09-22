<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('bisa masuk memakai email', function () {
    $user = User::factory()->create([
        'email' => 'warga@example.com',
        'phone' => null,
        'password' => Hash::make('password123'),
    ]);

    $response = $this->post('/login', [
        'login' => 'warga@example.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect('/menu');
    $this->assertAuthenticatedAs($user);
});

it('bisa masuk memakai nomor HP', function () {
    $user = User::factory()->create([
        'email' => null,
        'phone' => '081234567890',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->post('/login', [
        'login' => '081234567890',
        'password' => 'password123',
    ]);

    $response->assertRedirect('/menu');
    $this->assertAuthenticatedAs($user);
});

it('menolak kata sandi yang salah', function () {
    User::factory()->create([
        'email' => 'warga@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->post('/login', [
        'login' => 'warga@example.com',
        'password' => 'salah-sandi',
    ]);

    $response->assertSessionHasErrors();
    $this->assertGuest();
});

it('menolak akun yang diblokir walau kata sandi benar', function () {
    User::factory()->create([
        'email' => 'diblokir@example.com',
        'password' => Hash::make('password123'),
        'is_blocked' => true,
    ]);

    $response = $this->post('/login', [
        'login' => 'diblokir@example.com',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors();
    $this->assertGuest();
});
