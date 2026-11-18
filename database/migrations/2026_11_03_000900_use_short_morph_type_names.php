<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Polymorphic columns store short names ("user", "post") instead of class
 * names, matching the morph map enforced in AppServiceProvider, so renaming a
 * class never orphans a row again.
 */
return new class extends Migration
{
    /** @var array<string, list<string>> */
    private array $columns = [
        'model_has_roles' => ['model_type'],
        'model_has_permissions' => ['model_type'],
        'personal_access_tokens' => ['tokenable_type'],
        'comments' => ['commentable_type'],
        'reactions' => ['reactionable_type'],
        'reviews' => ['reviewable_type'],
        'topicables' => ['topicable_type'],
        'media' => ['model_type'],
    ];

    /** @var array<string, string> */
    private array $names = [
        'App\\Models\\User' => 'user',
        'App\\Models\\Post' => 'post',
        'App\\Models\\PointOfInterest' => 'point_of_interest',
        'App\\Models\\Experience' => 'experience',
        'App\\Models\\Comment' => 'comment',
    ];

    public function up(): void
    {
        $this->rename($this->names);
    }

    public function down(): void
    {
        $this->rename(array_flip($this->names));
    }

    /**
     * @param  array<string, string>  $names
     */
    private function rename(array $names): void
    {
        foreach ($this->columns as $table => $columns) {
            foreach ($columns as $column) {
                foreach ($names as $from => $to) {
                    DB::table($table)->where($column, $from)->update([$column => $to]);
                }
            }
        }
    }
};
