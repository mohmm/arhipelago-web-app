<?php

namespace Database\Seeders;

use App\Services\PermissionCatalog;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeds the three roles and every permission the policies define.
 *
 * Safe to run again: permissions that already exist are left alone, and a role
 * that already exists only receives permissions created by this very run, so
 * whatever an administrator changed in the panel is never overwritten.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function __construct(private PermissionCatalog $catalog) {}

    /**
     * Roles and permissions are mirrored on both guards: `web` for the admin
     * panel and `api` for the mobile app.
     *
     * @var list<string>
     */
    protected array $guards = ['web', 'api'];

    /**
     * Permission names created by this run, per guard.
     *
     * @var array<string, list<string>>
     */
    protected array $createdPermissions = [];

    /**
     * What only a super admin may do: manage roles, and delete or restore users.
     *
     * @var list<string>
     */
    protected array $superAdminOnlyPermissions = [
        'roles.view',
        'role.viewAny',
        'role.view',
        'role.create',
        'role.update',
        'role.delete',
        'user.delete',
        'user.restore',
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->createPermissions();

        foreach ($this->guards as $guard) {
            $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => $guard]);
            $role->syncPermissions(Permission::where('guard_name', $guard)->get());
        }

        $this->seedRole('admin', array_diff($this->catalog->allPermissionNames(), $this->superAdminOnlyPermissions));

        // Travellers using the mobile app hold no permission at all: what they
        // may do with their own posts, comments and reviews is decided by
        // ownership in the policies. Synced exactly on every run, so nothing
        // left over from an older setup can ever open the admin panel to them.
        foreach ($this->guards as $guard) {
            Role::firstOrCreate(['name' => 'customer', 'guard_name' => $guard])->syncPermissions([]);
        }
    }

    protected function createPermissions(): void
    {
        foreach ($this->catalog->allPermissionNames() as $name) {
            foreach ($this->guards as $guard) {
                $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);

                if ($permission->wasRecentlyCreated) {
                    $this->createdPermissions[$guard][] = $name;
                }
            }
        }
    }

    /**
     * Create the role with exactly these permissions, or, when it already
     * exists, grant it only the permissions this run created.
     *
     * @param  iterable<string>  $names
     */
    protected function seedRole(string $roleName, iterable $names): void
    {
        $names = array_values(array_unique([...$names]));

        foreach ($this->guards as $guard) {
            $role = Role::where('name', $roleName)->where('guard_name', $guard)->first();

            if ($role == null) {
                Role::create(['name' => $roleName, 'guard_name' => $guard])->syncPermissions(
                    Permission::where('guard_name', $guard)->whereIn('name', $names)->get()
                );

                continue;
            }

            $missing = array_values(array_intersect($this->createdPermissions[$guard] ?? [], $names));

            if ($missing != []) {
                $role->givePermissionTo(Permission::where('guard_name', $guard)->whereIn('name', $missing)->get());
            }
        }
    }
}
