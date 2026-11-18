<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Services\PermissionCatalog;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Roles are created on every guard so a user assigned one is recognised on
     * both session and token requests.
     *
     * @var list<string>
     */
    private const GUARDS = ['web', 'api'];

    /**
     * Roles the seeder owns, which cannot be edited or removed here: the
     * platform owner always holds every permission, so no change can lock
     * everyone out, and travellers never hold any, so no change can open the
     * admin panel to the app's users.
     */
    private const PROTECTED_ROLES = ['super_admin', 'customer'];

    public function __construct(private PermissionCatalog $catalog) {}

    /**
     * Display the roles and the permissions that can be assigned to them.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', Role::class);

        return Inertia::render('admin/roles/index', [
            'roles' => $this->roles(),
            'permissionGroups' => $this->permissionGroups(),
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $name = $request->validated('name');

        $this->syncRoleForAllGuards($name, $request->validated('permissions'));

        activity()
            ->causedBy($request->user())
            ->withProperties(['role' => $name, 'permissions' => $request->validated('permissions')])
            ->log('Role created');

        Toast::success(__('Role created successfully.'));

        return to_route('admin.roles.index');
    }

    /**
     * Update the permissions assigned to the role.
     */
    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);

        $this->guardWebRole($role);

        abort_if($this->isProtected($role), 403, __('The :role role cannot be modified.', ['role' => $role->name]));

        $this->syncRoleForAllGuards($role->name, $request->validated('permissions'));

        activity()
            ->causedBy($request->user())
            ->withProperties(['role' => $role->name, 'permissions' => $request->validated('permissions')])
            ->log('Role permissions updated');

        Toast::success(__('Role updated successfully.'));

        return to_route('admin.roles.index');
    }

    /**
     * Remove the role from every guard.
     */
    public function destroy(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        $this->guardWebRole($role);

        abort_if($this->isProtected($role), 403, __('The :role role cannot be deleted.', ['role' => $role->name]));

        // Surfaced as a validation error so Inertia returns it to the page,
        // which is the only feedback channel the admin screens use.
        if ($role->users()->exists()) {
            throw ValidationException::withMessages([
                'role' => __('This role is still assigned to users and cannot be deleted.'),
            ]);
        }

        $name = $role->name;

        Role::whereIn('guard_name', self::GUARDS)
            ->where('name', $name)
            ->get()
            ->each
            ->delete();

        activity()
            ->causedBy($request->user())
            ->withProperties(['role' => $name])
            ->log('Role deleted');

        Toast::success(__('Role deleted successfully.'));

        return to_route('admin.roles.index');
    }

    /**
     * Roles listed from the web guard, which is the guard the UI manages.
     *
     * @return array<int, array<string, mixed>>
     */
    private function roles(): array
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->withCount('users')
            ->with('permissions:id,name')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role): array => [
                'id' => $role->id,
                'name' => $role->name,
                'users_count' => $role->users_count,
                'permissions' => $role->permissions->pluck('name')->sort()->values()->all(),
                'is_protected' => $this->isProtected($role),
                'can_delete' => ! $this->isProtected($role) && $role->users_count == 0,
            ])
            ->all();
    }

    /**
     * Every assignable permission, grouped by the policy it belongs to.
     *
     * @return array<string, list<string>>
     */
    private function permissionGroups(): array
    {
        $names = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->pluck('name')
            ->all();

        return $this->catalog->group($names);
    }

    /**
     * The UI manages web-guard roles; the api-guard rows are kept in step by
     * name and are not addressable directly.
     */
    private function guardWebRole(Role $role): void
    {
        abort_unless($role->guard_name == 'web', 404);
    }

    private function isProtected(Role $role): bool
    {
        return in_array($role->name, self::PROTECTED_ROLES, true);
    }

    /**
     * Create the role on every guard and sync it to the given permissions.
     *
     * @param  array<int, string>  $permissionNames
     */
    private function syncRoleForAllGuards(string $roleName, array $permissionNames): void
    {
        foreach (self::GUARDS as $guard) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => $guard]);

            $role->syncPermissions(
                Permission::where('guard_name', $guard)
                    ->whereIn('name', $permissionNames)
                    ->get()
            );
        }
    }
}
