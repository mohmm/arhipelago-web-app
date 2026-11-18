<?php

namespace App\Enums;

/**
 * The four kinds of experience a destination can offer.
 */
enum ExperienceKind: string
{
    case Classic = 'classic';
    case Family = 'family';
    case CarFree = 'car_free';
    case Winter = 'winter';

    /**
     * An ORDER BY expression that lists kinds in the order they are declared,
     * which is the order the app shows them in.
     */
    public static function orderExpression(string $column = 'kind'): string
    {
        $branches = collect(self::cases())
            ->map(fn (self $kind, int $position): string => "WHEN '{$kind->value}' THEN {$position}")
            ->implode(' ');

        return "CASE {$column} {$branches} END";
    }

    /**
     * How this kind of day is travelled: a car-free day is walked, the others are driven.
     */
    public function travelMode(): TravelMode
    {
        return $this === self::CarFree ? TravelMode::Foot : TravelMode::Car;
    }

    /**
     * The experience's title in the given language, e.g. "Traseul clasic".
     */
    public function label(?string $locale = null): string
    {
        return __(match ($this) {
            self::Classic => 'Classic route',
            self::Family => 'With the family',
            self::CarFree => 'Car-free',
            self::Winter => 'In winter',
        }, locale: $locale);
    }
}
