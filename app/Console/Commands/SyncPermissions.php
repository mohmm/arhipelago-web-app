<?php

namespace App\Console\Commands;

use App\Services\PermissionCatalog;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class SyncPermissions extends Command
{
    protected $signature = 'permissions:sync
                            {--dry-run : Show what would change without writing anything}';

    protected $description = 'Reconcile the permission table with the application policies and section gates';

    /**
     * Guards permissions are created for.
     *
     * @var list<string>
     */
    private const GUARDS = ['web', 'api'];

    public function handle(PermissionCatalog $catalog): int
    {
        $this->info('Syncing permissions from policies...');
        $this->newLine();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $names = $catalog->allPermissionNames();

        if ($catalog->modelPermissionNames() == []) {
            $this->warn('No policies found — nothing to sync.');

            return self::SUCCESS;
        }

        $missing = 0;
        $skipped = 0;
        $rows = [];

        // Reported here but created by the seeder below, never by this command.
        // The seeder grants a role exactly the permissions its own run created,
        // which is how a new capability reaches `admin` without reverting an
        // edit made on the roles screen. Creating them up here would leave that
        // list empty and quietly strand every new policy with super_admin.
        foreach ($names as $name) {
            foreach (self::GUARDS as $guard) {
                $exists = Permission::where('name', $name)->where('guard_name', $guard)->exists();

                if ($exists) {
                    $skipped++;
                    $rows[] = [$name, $guard, '<fg=yellow>Skipped</>'];

                    continue;
                }

                $missing++;
                $rows[] = [$name, $guard, '<fg=green>Missing</>'];
            }
        }

        // Anything outside the catalogue can never be reached by a policy or a
        // section gate, so it is removed rather than left to mislead the roles
        // screen into offering a permission that does nothing.
        $stale = Permission::whereNotIn('name', $names)->get();

        foreach ($stale as $permission) {
            $rows[] = [$permission->name, $permission->guard_name, '<fg=red>Removed</>'];

            if (! $this->option('dry-run')) {
                $permission->delete();
            }
        }

        if ($this->output->isVerbose() || $this->option('dry-run')) {
            $this->table(['Permission', 'Guard', 'Status'], $rows);
        }

        $this->newLine();
        $this->info("Missing: {$missing} permissions (created by the seeder below)");
        $this->info("Skipped: {$skipped} (already exist)");
        $this->info("Removed: {$stale->count()} (no longer declared by a policy or section)");

        if ($this->option('dry-run')) {
            $this->newLine();
            $this->warn('Dry run - no changes were made.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('Seeding roles and section permissions...');
        $this->call('db:seed', ['--class' => 'RolesAndPermissionsSeeder', '--no-interaction' => true]);

        return self::SUCCESS;
    }
}
