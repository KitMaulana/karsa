<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->in('Unit');

function actingAsWarga(): \App\Models\User
{
    $user = \App\Models\User::factory()->create(['role' => \App\Enums\UserRole::Warga]);
    test()->actingAs($user);

    return $user;
}
