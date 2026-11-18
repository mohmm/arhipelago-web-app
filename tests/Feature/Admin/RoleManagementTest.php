<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function roleSuperAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    return $user;
}

function roleHolder(string $roleName): User
{
    $user = User::factory()->create();
    $user->assignRole($roleName);

    return $user;
}

/**
 * Assign a role the way Admin\UserController does — on every guard.
 */
function assignRoleOnAllGuards(User $user, string $roleName): void
{
    $user->roles()->attach(
        Role::whereIn('guard_name', ['web', 'api'])->where('name', $roleName)->pluck('id')
    );
}

it('lists roles and the grouped permission catalogue for a super admin', function () {
    actingAs(roleSuperAdmin())
        ->get('/admin/roles')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/roles/index')
            ->has('roles', 3)
            ->where('roles.0.name', 'admin')
            ->where('roles.1.name', 'customer')
            ->where('roles.2.name', 'super_admin')
            ->where('roles.2.is_protected', true)
            ->where('roles.2.can_delete', false)
            ->has('permissionGroups.sections')
            ->has('permissionGroups.post')
            ->has('permissionGroups.role')
            ->where('roles.1.is_protected', true));
});

it('denies the roles page to everyone except super admins', function () {
    actingAs(User::factory()->create())->get('/admin/roles')->assertForbidden();
    actingAs(roleHolder('admin'))->get('/admin/roles')->assertForbidden();
    actingAs(roleHolder('customer'))->get('/admin/roles')->assertForbidden();
});

it('creates a role on both guards with the given permissions', function () {
    actingAs(roleSuperAdmin())
        ->post('/admin/roles', [
            'name' => 'moderator',
            'permissions' => ['community.view', 'post.moderate'],
        ])
        ->assertRedirect('/admin/roles');

    foreach (['web', 'api'] as $guard) {
        $role = Role::findByName('moderator', $guard);

        expect($role->permissions->pluck('name')->sort()->values()->all())
            ->toBe(['community.view', 'post.moderate']);
    }
});

it('rejects an invalid, duplicate or unknown-permission role', function () {
    $superAdmin = roleSuperAdmin();

    actingAs($superAdmin)
        ->post('/admin/roles', ['name' => 'Hotel Manager', 'permissions' => []])
        ->assertSessionHasErrors('name');

    actingAs($superAdmin)
        ->post('/admin/roles', ['name' => 'admin', 'permissions' => []])
        ->assertSessionHasErrors('name');

    actingAs($superAdmin)
        ->post('/admin/roles', ['name' => 'valid_role', 'permissions' => ['does.not_exist']])
        ->assertSessionHasErrors('permissions.0');

    expect(Role::where('name', 'valid_role')->exists())->toBeFalse();
});

it('syncs updated permissions across both guards', function () {
    $role = Role::findByName('admin', 'web');

    actingAs(roleSuperAdmin())
        ->patch("/admin/roles/{$role->id}", ['permissions' => ['community.view']])
        ->assertRedirect('/admin/roles');

    foreach (['web', 'api'] as $guard) {
        expect(Role::findByName('admin', $guard)->permissions->pluck('name')->all())
            ->toBe(['community.view']);
    }
});

it('refuses to modify or delete the super admin role', function () {
    $role = Role::findByName('super_admin', 'web');
    $permissionCount = $role->permissions->count();

    actingAs(roleSuperAdmin())
        ->patch("/admin/roles/{$role->id}", ['permissions' => []])
        ->assertForbidden();

    actingAs(roleSuperAdmin())
        ->delete("/admin/roles/{$role->id}")
        ->assertForbidden();

    expect(Role::findByName('super_admin', 'web')->permissions)->toHaveCount($permissionCount);
});

it('refuses to delete a role that is still assigned to a user', function () {
    $role = Role::findByName('admin', 'web');
    assignRoleOnAllGuards(User::factory()->create(), 'admin');

    actingAs(roleSuperAdmin())
        ->delete("/admin/roles/{$role->id}")
        ->assertSessionHasErrors('role');

    expect(Role::where('name', 'admin')->exists())->toBeTrue();
});

it('refuses to give the traveller role any permission', function () {
    $role = Role::findByName('customer', 'web');

    actingAs(roleSuperAdmin())
        ->patch("/admin/roles/{$role->id}", ['permissions' => ['admin.view']])
        ->assertForbidden();

    expect($role->fresh()->permissions)->toBeEmpty();
});

it('deletes an unused role from every guard', function () {
    actingAs(roleSuperAdmin())->post('/admin/roles', ['name' => 'temp_role', 'permissions' => []]);

    $role = Role::findByName('temp_role', 'web');

    actingAs(roleSuperAdmin())
        ->delete("/admin/roles/{$role->id}")
        ->assertRedirect('/admin/roles');

    expect(Role::where('name', 'temp_role')->count())->toBe(0);
});

it('counts an assigned user once even though the role exists on both guards', function () {
    assignRoleOnAllGuards(User::factory()->create(), 'customer');

    actingAs(roleSuperAdmin())
        ->get('/admin/roles')
        ->assertInertia(fn ($page) => $page
            ->where('roles.1.name', 'customer')
            ->where('roles.1.users_count', 1));
});

it('does not expose api-guard roles to the web UI', function () {
    $apiRole = Role::findByName('customer', 'api');

    actingAs(roleSuperAdmin())
        ->patch("/admin/roles/{$apiRole->id}", ['permissions' => []])
        ->assertNotFound();

    actingAs(roleSuperAdmin())
        ->delete("/admin/roles/{$apiRole->id}")
        ->assertNotFound();
});

it('records role changes in the activity log', function () {
    actingAs(roleSuperAdmin())->post('/admin/roles', ['name' => 'logged_role', 'permissions' => []]);

    expect(Activity::where('description', 'Role created')->exists())->toBeTrue();
});

it('stops a traveller from promoting itself through the user admin screen', function () {
    $customer = roleHolder('customer');

    expect($customer->can('admin.view'))->toBeFalse();

    actingAs($customer)
        ->get("/admin/users/{$customer->id}/edit")
        ->assertForbidden();

    actingAs($customer)
        ->put("/admin/users/{$customer->id}", [
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'email' => $customer->email,
            'role' => 'admin',
        ])
        ->assertForbidden();

    expect($customer->fresh()->getRoleNames()->unique()->all())->toBe(['customer'])
        ->and($customer->fresh()->can('user.viewAny'))->toBeFalse();
});

it('stops anyone from changing their own role, however privileged', function () {
    // A hand-built role that can administer users but is not platform staff.
    $role = Role::create(['name' => 'user_manager', 'guard_name' => 'web']);
    $role->syncPermissions(
        Permission::whereIn('name', ['admin.view', 'user.viewAny', 'user.view', 'user.update'])
            ->where('guard_name', 'web')
            ->get()
    );

    $manager = User::factory()->create();
    $manager->assignRole($role);

    actingAs($manager)
        ->put("/admin/users/{$manager->id}", [
            'first_name' => $manager->first_name,
            'last_name' => $manager->last_name,
            'email' => $manager->email,
            'role' => 'admin',
        ])
        ->assertSessionHasErrors('role');

    expect($manager->fresh()->getRoleNames()->unique()->all())->toBe(['user_manager']);

    // ...but it can still administer somebody else.
    $other = User::factory()->create();
    $other->assignRole('customer');

    actingAs($manager)
        ->put("/admin/users/{$other->id}", [
            'first_name' => $other->first_name,
            'last_name' => $other->last_name,
            'email' => $other->email,
            'role' => 'customer',
        ])
        ->assertSessionHasNoErrors();
});

it('offers no assignable roles to someone who cannot administer users', function () {
    expect(roleHolder('customer')->assignableRoleNames())->toBe([])
        ->and(User::factory()->create()->assignableRoleNames())->toBe([])
        ->and(roleHolder('admin')->assignableRoleNames())->toContain('customer')
        ->and(roleHolder('admin')->assignableRoleNames())->not->toContain('super_admin');
});

it('never reverts role permissions edited through the UI when reseeded', function () {
    $role = Role::findByName('admin', 'web');

    actingAs(roleSuperAdmin())
        ->patch("/admin/roles/{$role->id}", ['permissions' => ['community.view']])
        ->assertRedirect('/admin/roles');

    $this->seed(RolesAndPermissionsSeeder::class);

    expect(Role::findByName('admin', 'web')->permissions->pluck('name')->all())->toBe(['community.view']);
});

it('leaves roles created in the UI intact when permissions are reconciled', function () {
    actingAs(roleSuperAdmin())->post('/admin/roles', [
        'name' => 'moderator',
        'permissions' => ['admin.view', 'community.view'],
    ]);

    $this->artisan('permissions:sync')->assertSuccessful();

    expect(Role::findByName('moderator', 'web')->permissions->pluck('name')->sort()->values()->all())
        ->toBe(['admin.view', 'community.view']);
});

it('keeps the permission catalogue in step with the policies', function () {
    // Every permission a policy asserts must exist, or hasPermissionTo() throws
    // instead of denying. The seeder alone has to be sufficient for that.
    expect(Permission::where('guard_name', 'web')->where('name', 'post.moderate')->exists())->toBeTrue()
        ->and(Permission::where('guard_name', 'web')->where('name', 'role.viewAny')->exists())->toBeTrue()
        ->and(Permission::where('guard_name', 'api')->where('name', 'role.viewAny')->exists())->toBeTrue();
});
