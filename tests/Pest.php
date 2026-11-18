<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Every feature test runs against a fresh in-memory database.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

/**
 * Seed the roles and permissions, which most admin-panel tests need.
 */
function seedRoles(): void
{
    test()->seed(RolesAndPermissionsSeeder::class);
}

/**
 * A member of staff with the given role, the roles seeded first.
 */
function staffMember(string $role = 'admin'): User
{
    seedRoles();

    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}
