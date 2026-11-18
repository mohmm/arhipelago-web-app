<?php

namespace Database\Seeders;

use App\Models\PointOfInterestCategory;
use Illuminate\Database\Seeder;

/**
 * The categories travellers filter places by, in the order the app shows them.
 *
 * Matched on the slug, so running it again updates the names without
 * duplicating anything or touching categories added in the admin panel.
 */
class PointOfInterestCategorySeeder extends Seeder
{
    /**
     * @var list<array{slug: string, name_ro: string, name_en: string, name_ar: string, icon: string}>
     */
    private array $categories = [
        ['slug' => 'castles-and-fortresses', 'name_ro' => 'Castele și cetăți', 'name_en' => 'Castles and fortresses', 'name_ar' => 'قلاع وحصون', 'icon' => 'castle'],
        ['slug' => 'churches-and-monasteries', 'name_ro' => 'Biserici și mănăstiri', 'name_en' => 'Churches and monasteries', 'name_ar' => 'كنائس وأديرة', 'icon' => 'church'],
        ['slug' => 'museums', 'name_ro' => 'Muzee', 'name_en' => 'Museums', 'name_ar' => 'متاحف', 'icon' => 'landmark'],
        ['slug' => 'nature', 'name_ro' => 'Natură', 'name_en' => 'Nature', 'name_ar' => 'طبيعة', 'icon' => 'mountain'],
        ['slug' => 'historic-centres', 'name_ro' => 'Centre istorice', 'name_en' => 'Historic centres', 'name_ar' => 'مراكز تاريخية', 'icon' => 'building-2'],
    ];

    public function run(): void
    {
        foreach ($this->categories as $order => $category) {
            PointOfInterestCategory::withTrashed()->updateOrCreate(
                ['slug' => $category['slug']],
                $category + ['is_active' => true, 'sort_order' => $order + 1, 'deleted_at' => null],
            );
        }
    }
}
