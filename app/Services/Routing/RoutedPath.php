<?php

namespace App\Services\Routing;

/**
 * One answer from the router: the line, what it costs, and what it had to move.
 *
 * `legs` holds one entry per gap between consecutive stops, so a page can say
 * how far it is from the third stop to the fourth rather than only what the
 * whole day comes to — and, since each leg carries its own line, can show which
 * road that is. `snapped` holds one entry per stop, carrying how far OSRM moved
 * it to reach a routable way — the number that says whether a stop has a road to
 * it at all.
 */
class RoutedPath
{
    /**
     * @param  list<array{distance_m: int, duration_s: int, polyline: string}>  $legs
     * @param  list<array{metres: int, latitude: float, longitude: float}>  $snapped
     */
    public function __construct(
        public readonly int $distanceMetres,
        public readonly int $durationSeconds,
        public readonly string $polyline,
        public readonly array $legs,
        public readonly array $snapped,
    ) {}

    /** The furthest any one stop had to be moved to reach a road. */
    public function worstSnapMetres(): int
    {
        return $this->snapped == [] ? 0 : max(array_column($this->snapped, 'metres'));
    }
}
