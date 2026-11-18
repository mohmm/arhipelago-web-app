<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('seeds super_admin and admin roles for both guards', function () {
    expect(Role::where('name', 'super_admin')->where('guard_name', 'web')->exists())->toBeTrue()
        ->and(Role::where('name', 'admin')->where('guard_name', 'web')->exists())->toBeTrue()
        ->and(Role::where('name', 'super_admin')->where('guard_name', 'api')->exists())->toBeTrue()
        ->and(Role::where('name', 'admin')->where('guard_name', 'api')->exists())->toBeTrue();
});

it('grants all permissions to super_admin but withholds super-admin-only from admin', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    expect($superAdmin->can('user.delete'))->toBeTrue()
        ->and($admin->can('user.delete'))->toBeFalse()
        ->and($admin->can('experience.update'))->toBeTrue();
});

it('resolves admin helper methods on the user model', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');

    $regular = User::factory()->create();

    expect($admin->canAccessAdminPanel())->toBeTrue()
        ->and($admin->isSuperAdmin())->toBeFalse()
        ->and($superAdmin->canAccessAdminPanel())->toBeTrue()
        ->and($superAdmin->isSuperAdmin())->toBeTrue()
        ->and($regular->canAccessAdminPanel())->toBeFalse();
});

it('gates routes behind the admin middleware', function () {
    Route::middleware(['web', 'auth', 'admin'])->get('/_admin-check', fn () => 'ok');

    $regular = User::factory()->create();
    $this->actingAs($regular)->get('/_admin-check')->assertForbidden();

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin)->get('/_admin-check')->assertOk();
});

it('opens the admin panel on the admin.view permission rather than a role name', function () {
    Route::middleware(['web', 'auth', 'admin'])->get('/_admin-check', fn () => 'ok');

    $role = Role::create(['name' => 'panel_only', 'guard_name' => 'web']);
    $role->givePermissionTo(Permission::findByName('admin.view', 'web'));

    $user = User::factory()->create();
    $user->assignRole($role);

    expect($user->hasRole(['super_admin', 'admin']))->toBeFalse();

    $this->actingAs($user)->get('/_admin-check')->assertOk();
});

it('seeds the traveller role on both guards without a single permission', function () {
    $traveller = User::factory()->create();
    $traveller->assignRole('customer');

    expect(Role::where('name', 'customer')->where('guard_name', 'web')->exists())->toBeTrue()
        ->and(Role::where('name', 'customer')->where('guard_name', 'api')->exists())->toBeTrue()
        ->and($traveller->getAllPermissions())->toBeEmpty()
        ->and($traveller->canAccessAdminPanel())->toBeFalse();
});

it('takes back any permission a traveller role held before', function () {
    Role::findByName('customer', 'web')->givePermissionTo('admin.view');

    $this->seed(RolesAndPermissionsSeeder::class);

    expect(Role::findByName('customer', 'web')->permissions)->toBeEmpty();
});

it('withholds role management from the admin role', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    expect($admin->can('roles.view'))->toBeFalse()
        ->and($admin->can('role.viewAny'))->toBeFalse()
        ->and($admin->can('role.update'))->toBeFalse()
        ->and($admin->can('role.delete'))->toBeFalse()
        // ...but it keeps the rest of the back-office.
        ->and($admin->can('post.moderate'))->toBeTrue()
        ->and($admin->can('users.view'))->toBeTrue();
});

it('shares the authenticated user role and permissions with inertia', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)->get('/')
        ->assertInertia(fn ($page) => $page
            ->where('auth.role', 'admin')
            ->has('auth.permissions')
        );
});

it('shares each permission once even though roles exist on both guards', function () {
    $user = User::factory()->create();
    $user->roles()->attach(
        Role::whereIn('guard_name', ['web', 'api'])->where('name', 'admin')->pluck('id')
    );

    expect($user->getAllPermissions()->pluck('name')->count())
        ->toBeGreaterThan($user->getAllPermissions()->pluck('name')->unique()->count());

    $this->actingAs($user)->get('/')
        ->assertInertia(function ($page) {
            $permissions = $page->toArray()['props']['auth']['permissions'];

            expect($permissions)->toBe(array_values(array_unique($permissions)))
                ->and($permissions)->toContain('admin.view');

            return $page;
        });
});
