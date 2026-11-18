<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

/**
 * Single source of truth for the application's permission vocabulary.
 *
 * Model permissions are derived from the policies: every public ability on an
 * `App\Policies\{Model}Policy` becomes a `{model}.{ability}` permission. Both
 * `php artisan permissions:sync` and the roles admin screen read from here, so
 * the names that are generated and the way they are grouped can never drift.
 *
 * Grouping falls out of the same reflection: a permission whose prefix matches
 * a policy belongs to that model, and anything else — `dashboard.view`,
 * `admin.view`, `leads.view` — is a section permission that gates navigation
 * rather than a record. That is why adding a policy is all it takes for its
 * permissions to appear under their own heading in the admin UI.
 */
class PermissionCatalog
{
    /**
     * Policy methods that never represent an ability.
     *
     * @var list<string>
     */
    private const IGNORED_METHODS = ['__construct', '__call', '__get', '__set', 'before', 'after'];

    /**
     * Group key used for permissions that do not belong to a policy.
     */
    public const SECTION_GROUP = 'sections';

    /**
     * Section permissions gate navigation and whole feature areas rather than a
     * record, so they have no policy behind them and are declared here.
     *
     * @var list<string>
     */
    private const SECTION_PERMISSIONS = [
        'dashboard.view',
        // Entering the admin panel at all.
        'admin.view',
        // Points of interest, experiences, destinations, categories and topics.
        'content.view',
        // Posts, comments and reviews, for moderation.
        'community.view',
        'users.view',
        'roles.view',
        // Maya's instructions and the legal documents.
        'settings.view',
    ];

    /**
     * Model keys mapped to their abilities, e.g. `['assistant' => ['view', ...]]`.
     *
     * @var array<string, list<string>>|null
     */
    private ?array $abilities = null;

    /**
     * Every ability declared by the policies, keyed by model.
     *
     * @return array<string, list<string>>
     */
    public function abilitiesByModel(): array
    {
        return $this->abilities ??= $this->reflectPolicies();
    }

    /**
     * The permissions that gate navigation rather than a record.
     *
     * @return list<string>
     */
    public function sectionPermissionNames(): array
    {
        return self::SECTION_PERMISSIONS;
    }

    /**
     * Every permission the application expects to exist. Anything outside this
     * set is stale and can never be reached by a policy or a section gate.
     *
     * @return list<string>
     */
    public function allPermissionNames(): array
    {
        return array_values(array_unique([
            ...self::SECTION_PERMISSIONS,
            ...$this->modelPermissionNames(),
        ]));
    }

    /**
     * Every `{model}.{ability}` permission name derived from the policies.
     *
     * @return list<string>
     */
    public function modelPermissionNames(): array
    {
        $names = [];

        foreach ($this->abilitiesByModel() as $model => $abilities) {
            foreach ($abilities as $ability) {
                $names[] = "{$model}.{$ability}";
            }
        }

        return $names;
    }

    /**
     * Group permission names by the model they belong to.
     *
     * Names whose prefix has no matching policy are collected under
     * {@see self::SECTION_GROUP}. Groups and the names inside them are sorted
     * so the admin screen renders in a stable order.
     *
     * @param  iterable<string>  $names
     * @return array<string, list<string>>
     */
    public function group(iterable $names): array
    {
        $models = $this->abilitiesByModel();
        $groups = [];

        foreach ($names as $name) {
            $prefix = Str::before($name, '.');
            $group = array_key_exists($prefix, $models) ? $prefix : self::SECTION_GROUP;

            $groups[$group][] = $name;
        }

        foreach ($groups as $group => $groupNames) {
            sort($groupNames);
            $groups[$group] = array_values($groupNames);
        }

        // Sections first — they gate the navigation — then models alphabetically.
        uksort($groups, function (string $a, string $b): int {
            if ($a == self::SECTION_GROUP || $b == self::SECTION_GROUP) {
                return $a == self::SECTION_GROUP ? -1 : 1;
            }

            return $a <=> $b;
        });

        return $groups;
    }

    /**
     * Reflect over `app/Policies` to collect each model's abilities.
     *
     * @return array<string, list<string>>
     */
    private function reflectPolicies(): array
    {
        $path = app_path('Policies');

        if (! File::isDirectory($path)) {
            return [];
        }

        $abilities = [];

        foreach (File::files($path) as $file) {
            $class = 'App\\Policies\\'.pathinfo($file->getFilename(), PATHINFO_FILENAME);

            if (! class_exists($class)) {
                continue;
            }

            $methods = $this->publicAbilities($class);

            if ($methods == []) {
                continue;
            }

            $abilities[$this->modelKey($class)] = $methods;
        }

        ksort($abilities);

        return $abilities;
    }

    /**
     * The permission prefix for a policy class, e.g. `PointOfInterestPolicy` → `point_of_interest`.
     *
     * A policy may declare a `PERMISSION_PREFIX` constant when the domain name
     * differs from the model class name.
     */
    private function modelKey(string $class): string
    {
        if (defined("{$class}::PERMISSION_PREFIX")) {
            return (string) constant("{$class}::PERMISSION_PREFIX");
        }

        return Str::snake(Str::replaceLast('Policy', '', class_basename($class)));
    }

    /**
     * The abilities a policy class declares itself, ignoring inherited and hook methods.
     *
     * @return list<string>
     */
    private function publicAbilities(string $class): array
    {
        $abilities = [];

        foreach ((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getDeclaringClass()->getName() != $class) {
                continue;
            }

            if (in_array($method->getName(), self::IGNORED_METHODS)) {
                continue;
            }

            $abilities[] = $method->getName();
        }

        sort($abilities);

        return $abilities;
    }
}
