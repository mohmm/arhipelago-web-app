<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Notifications\Auth\InvitationNotification;
use App\Services\Notifications\Notifier;
use App\Support\Toast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(private Notifier $notifier) {}

    /**
     * Display a listing of users.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $filters = [
            'search' => trim((string) $request->input('search')) ?: null,
            'role' => $request->input('role') ?: null,
            'verified' => $request->input('verified') ?: null,
        ];

        $users = User::query()
            ->with('roles')
            ->when($filters['search'], function (Builder $query, string $search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filters['role'], function (Builder $query, string $role): void {
                if ($role == 'none') {
                    $query->doesntHave('roles');
                } else {
                    $query->whereHas('roles', fn (Builder $inner) => $inner->where('name', $role));
                }
            })
            ->when($filters['verified'] == 'verified', fn (Builder $query) => $query->whereNotNull('email_verified_at'))
            ->when($filters['verified'] == 'unverified', fn (Builder $query) => $query->whereNull('email_verified_at'))
            ->orderBy('first_name')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (User $user) => $this->formatItem($user));

        return Inertia::render('admin/users/index', [
            'items' => $users,
            'filters' => $filters,
            'roles' => $this->getFilterableRoles(),
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('admin/users/create', [
            'roles' => $this->getAssignableRoles(),
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $chosenPassword = $request->validated('password');

        $user = User::create([
            'first_name' => $request->validated('first_name'),
            'last_name' => $request->validated('last_name'),
            'email' => $request->validated('email'),
            // A password nobody knows when one was not supplied. The account
            // cannot be signed into until the invitation link is used, and
            // leaving the column nullable instead would make a null password a
            // state the rest of the application has to keep guarding against.
            'password' => Hash::make($chosenPassword ?? Str::random(40)),
        ]);

        // Assigned rather than mass assigned: `email_verified_at` is not
        // fillable, so passing it to `create()` dropped it silently — which
        // went unnoticed for as long as nothing checked verification.
        // A password typed here by somebody who is present is the only proof
        // of the address there will be; an invited user proves it by opening
        // the invitation.
        if ($chosenPassword != null) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        if ($request->validated('role')) {
            $this->assignRoleForBothGuards($user, $request->validated('role'));
        }

        if ($chosenPassword == null) {
            $this->invite($user, $request->user());

            Toast::success(__('Invitation sent to :email.', ['email' => $user->email]));

            return to_route('admin.users.index');
        }

        Toast::success(__('User created successfully.'));

        return to_route('admin.users.index');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): Response
    {
        $this->authorize('view', $user);

        return Inertia::render('admin/users/show', [
            'item' => $this->formatItem($user),
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        return Inertia::render('admin/users/edit', [
            'item' => $this->formatItem($user),
            'roles' => $this->getAssignableRoles(),
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = [
            'first_name' => $request->validated('first_name'),
            'last_name' => $request->validated('last_name'),
            'email' => $request->validated('email'),
        ];

        if ($request->validated('password')) {
            $data['password'] = Hash::make($request->validated('password'));
        }

        $user->update($data);

        if ($request->has('role') && $request->validated('role') != $user->getRoleNames()->first()) {
            $this->syncRolesForBothGuards($user, $request->validated('role'));
        }

        Toast::success(__('User updated successfully.'));

        return to_route('admin.users.index');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        Toast::success(__('User deleted successfully.'));

        return to_route('admin.users.index');
    }

    /**
     * Get the roles that the current user may assign.
     *
     * @return array<int, array{value: string, label: string}>
     */
    private function getAssignableRoles(): array
    {
        return collect(auth()->user()->assignableRoleNames())
            ->map(fn (string $name) => [
                'value' => $name,
                'label' => ucwords(str_replace('_', ' ', $name)),
            ])->toArray();
    }

    /**
     * Get every role as filter options.
     *
     * @return array<int, array{value: string, label: string}>
     */
    private function getFilterableRoles(): array
    {
        return Role::where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role) => [
                'value' => $role->name,
                'label' => ucwords(str_replace('_', ' ', $role->name)),
            ])->toArray();
    }

    /**
     * Assign a role for both the web and api guards.
     */
    /**
     * Send somebody the link that lets them choose their own password.
     *
     * An ordinary reset token, deliberately. A bespoke invitation token would
     * be a second credential with its own expiry, its own storage and its own
     * chance of being wrong — for a link that does exactly what the reset flow
     * already does safely.
     */
    private function invite(User $user, ?User $invitedBy): void
    {
        $this->notifier->send($user, new InvitationNotification(
            Password::broker()->createToken($user),
            $invitedBy == null ? null : trim($invitedBy->first_name.' '.$invitedBy->last_name),
        ));
    }

    private function assignRoleForBothGuards(User $user, string $roleName): void
    {
        $webRole = Role::where('name', $roleName)->where('guard_name', 'web')->first();
        $apiRole = Role::where('name', $roleName)->where('guard_name', 'api')->first();

        $roleIds = array_filter([$webRole?->id, $apiRole?->id]);

        if (! empty($roleIds)) {
            $user->roles()->attach($roleIds);
        }
    }

    /**
     * Sync a role for both the web and api guards.
     */
    private function syncRolesForBothGuards(User $user, string $roleName): void
    {
        $webRole = Role::where('name', $roleName)->where('guard_name', 'web')->first();
        $apiRole = Role::where('name', $roleName)->where('guard_name', 'api')->first();

        $roleIds = array_filter([$webRole?->id, $apiRole?->id]);

        $user->roles()->sync($roleIds);
    }

    /**
     * Format a user for the frontend.
     *
     * @return array<string, mixed>
     */
    private function formatItem(User $user): array
    {
        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'role' => $user->getRoleNames()->first(),
            'email_verified_at' => $user->email_verified_at?->toISOString(),
            'created_at' => $user->created_at?->toISOString(),
            'updated_at' => $user->updated_at?->toISOString(),
            'can_delete' => auth()->user()?->can('delete', $user) ?? false,
        ];
    }
}
