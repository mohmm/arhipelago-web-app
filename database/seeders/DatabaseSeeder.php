<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * The reference data (roles, categories, legal documents) is seeded in
     * every environment. The demo administrator is never created in
     * production, where accounts are made from the admin panel.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(PointOfInterestCategorySeeder::class);
        $this->call(TopicSeeder::class);
        $this->call(LegalDocumentSeeder::class);

        if (app()->environment('production')) {
            return;
        }

        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'first_name' => 'Test',
                'last_name' => 'User',
                'password' => 'password',
            ],
        );

        if (! $user->hasRole('super_admin')) {
            $user->assignRole('super_admin');
        }
    }
}
