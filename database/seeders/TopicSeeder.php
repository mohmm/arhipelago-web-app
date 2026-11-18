<?php

namespace Database\Seeders;

use App\Models\Topic;
use Illuminate\Database\Seeder;

/**
 * The themes places and experiences are tagged with.
 *
 * Matched on the slug, so running it again updates the names without
 * duplicating anything or touching themes added in the admin panel.
 */
class TopicSeeder extends Seeder
{
    /**
     * @var list<array{slug: string, name_ro: string, name_en: string, name_ar: string, icon: string}>
     */
    private array $topics = [
        ['slug' => 'history', 'name_ro' => 'Istorie', 'name_en' => 'History', 'name_ar' => 'تاريخ', 'icon' => 'scroll'],
        ['slug' => 'architecture', 'name_ro' => 'Arhitectură', 'name_en' => 'Architecture', 'name_ar' => 'عمارة', 'icon' => 'columns-3'],
        ['slug' => 'faith', 'name_ro' => 'Credință', 'name_en' => 'Faith', 'name_ar' => 'إيمان', 'icon' => 'church'],
        ['slug' => 'nature', 'name_ro' => 'Natură', 'name_en' => 'Nature', 'name_ar' => 'طبيعة', 'icon' => 'trees'],
        ['slug' => 'viewpoints', 'name_ro' => 'Panorame', 'name_en' => 'Viewpoints', 'name_ar' => 'إطلالات', 'icon' => 'binoculars'],
        ['slug' => 'family', 'name_ro' => 'Pentru familie', 'name_en' => 'For families', 'name_ar' => 'للعائلات', 'icon' => 'baby'],
        ['slug' => 'hiking', 'name_ro' => 'Drumeție', 'name_en' => 'Hiking', 'name_ar' => 'مشي لمسافات طويلة', 'icon' => 'footprints'],
        ['slug' => 'winter', 'name_ro' => 'Iarna', 'name_en' => 'Winter', 'name_ar' => 'الشتاء', 'icon' => 'snowflake'],
    ];

    public function run(): void
    {
        foreach ($this->topics as $order => $topic) {
            Topic::withTrashed()->updateOrCreate(
                ['slug' => $topic['slug']],
                $topic + ['is_active' => true, 'sort_order' => $order + 1, 'deleted_at' => null],
            );
        }
    }
}
