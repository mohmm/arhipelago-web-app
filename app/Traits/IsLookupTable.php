<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait IsLookupTable
{
    use Translatable;

    /**
     * Boot the IsLookupTable trait.
     */
    public static function bootIsLookupTable(): void
    {
        static::creating(function ($model): void {
            if (empty($model->sort_order)) {
                $model->sort_order = static::query()->max('sort_order') + 1;
            }
        });
    }

    /**
     * Initialize the IsLookupTable trait.
     */
    public function initializeIsLookupTable(): void
    {
        $this->mergeFillable([
            'slug',
            'name_ro',
            'name_en',
            'name_ar',
            'is_active',
            'sort_order',
        ]);

        $this->mergeCasts([
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);
    }

    /**
     * Scope to only active records.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort_order.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Find a record by its slug.
     */
    public static function findBySlug(string $slug): ?static
    {
        return static::query()->where('slug', $slug)->first();
    }

    /**
     * Options keyed by slug rather than id, with the badge colour when the
     * table carries one.
     *
     * Slugs travel to the frontend so a filter can be linked to, bookmarked
     * and read in a URL; an autoincrement id in a query string means nothing
     * to anyone and changes between environments.
     *
     * @return array<int, array{value: string, label: string, color: string|null}>
     */
    public static function slugOptions(): array
    {
        return static::query()
            ->active()
            ->ordered()
            ->get()
            ->map(fn ($item): array => [
                'value' => $item->slug,
                'label' => $item->translated_name,
                'color' => $item->color ?? null,
            ])
            ->all();
    }

    /**
     * Get options formatted for select dropdowns.
     *
     * @return array<int, array{value: int, label: string}>
     */
    public static function getSelectOptions(): array
    {
        return static::query()
            ->active()
            ->ordered()
            ->get()
            ->map(fn ($item) => [
                'value' => $item->id,
                'label' => $item->translated_name,
            ])
            ->toArray();
    }
}
