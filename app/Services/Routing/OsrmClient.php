<?php

namespace App\Services\Routing;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Asks the local OSRM containers for a route through a list of stops.
 *
 * Called during generation and never during a request. The containers hold the
 * whole of Romania from a Geofabrik extract and answer in about a millisecond,
 * which is exactly why nothing may depend on them being up: an itinerary page
 * has to render from the database on a machine where Docker was never installed.
 *
 * Two things about the request are easy to get wrong and both are silent:
 *
 * - **Coordinates go in as `lng,lat`.** The wrong order returns a valid route
 *   through Kazakhstan with a plausible distance, so the answer is checked
 *   against the first coordinate before it is believed.
 * - **The path segment stays `/driving/` on both instances.** OSRM fixes the
 *   profile when it builds the graph, so the walking container answers walking
 *   routes to a URL that says driving, and refuses `/foot/` with a 400.
 */
class OsrmClient
{
    /**
     * How far the decoded line may start from the first snapped waypoint.
     *
     * Metres, and only a decoder check. OSRM's own `waypoints[].location` is
     * where it snapped the stop to, and the geometry it returns begins there, so
     * anything beyond a rounding error means the polyline was decoded wrongly or
     * a coordinate pair went in transposed.
     */
    private const GEOMETRY_TOLERANCE_METRES = 50;

    /**
     * Route through the given stops, in the order given.
     *
     * @param  list<array{float, float}>  $stops  latitude, longitude pairs
     * @param  'car'|'foot'  $profile
     *
     * @throws RuntimeException when the router cannot be reached or disbelieved
     */
    public function route(array $stops, string $profile = 'car'): RoutedPath
    {
        if (count($stops) < 2) {
            throw new RuntimeException('A route needs at least two stops.');
        }

        $base = rtrim((string) config("services.osrm.profiles.{$profile}"), '/');

        if ($base == '') {
            throw new RuntimeException("No OSRM URL is configured for the {$profile} profile.");
        }

        // lng,lat, semicolon separated — the order every OSRM mistake starts with.
        $path = implode(';', array_map(
            fn (array $stop): string => sprintf('%.6F,%.6F', $stop[1], $stop[0]),
            $stops,
        ));

        try {
            $response = Http::timeout((int) config('services.osrm.timeout'))
                ->get("{$base}/route/v1/driving/{$path}", [
                    'overview' => 'full',
                    'geometries' => 'polyline6',
                    /*
                     * Not for the instructions, which this product never gives —
                     * for the geometry. A leg's own line is the only thing OSRM
                     * will not hand over any other way: `legs[].distance` says how
                     * far it is from the third stop to the fourth, and only
                     * `legs[].steps[].geometry` says along which road. Asked for
                     * during generation, thrown away except for that.
                     */
                    'steps' => 'true',
                ]);
        } catch (Throwable $exception) {
            throw new RuntimeException("The {$profile} router did not answer: {$exception->getMessage()}", previous: $exception);
        }

        $body = $response->json();

        /*
         * Read the body even when the status is not 2xx.
         *
         * OSRM answers a refusal it understands with an HTTP 400 and a `code` that
         * says which refusal it is — `NoRoute` for two points its graph cannot
         * connect, `NoSegment` for a point with no road near it at all. Bailing on
         * the status alone reported "the car router answered 400" for sixteen
         * communes and threw away the one word that explains them.
         */
        if (! is_array($body)) {
            throw new RuntimeException("The {$profile} router answered {$response->status()} with no readable body.");
        }

        return $this->read($body, $stops, $profile);
    }

    /**
     * Which of these stops the router can actually get to from the first point.
     *
     * One request rather than one per stop: OSRM's table service answers the whole
     * row from a single source and puts `null` where there is no route, which is
     * exactly the question. Used only on the failure path — a `NoRoute` says the set
     * cannot be routed and says nothing about which member is at fault, and this is
     * how that is found out rather than guessed.
     *
     * A router that cannot answer at all is reported as everything being reachable,
     * because the caller is already handling a failure and a second one here would
     * replace a route with silence.
     *
     * @param  list<array{float, float}>  $stops  latitude, longitude pairs, the first being the origin
     * @param  'car'|'foot'  $profile
     * @return list<bool> one per stop, the origin included
     */
    public function reachable(array $stops, string $profile = 'car'): array
    {
        $base = rtrim((string) config("services.osrm.profiles.{$profile}"), '/');

        $path = implode(';', array_map(
            fn (array $stop): string => sprintf('%.6F,%.6F', $stop[1], $stop[0]),
            $stops,
        ));

        try {
            $response = Http::timeout((int) config('services.osrm.timeout'))
                ->get("{$base}/table/v1/driving/{$path}", ['sources' => 0, 'annotations' => 'duration']);

            $durations = $response->json('durations.0');
        } catch (Throwable) {
            $durations = null;
        }

        if (! is_array($durations) || count($durations) != count($stops)) {
            return array_fill(0, count($stops), true);
        }

        return array_map(fn (mixed $duration): bool => $duration !== null, array_values($durations));
    }

    /**
     * How long it takes to drive from one point to each of many, and how far each of
     * them was from a road.
     *
     * The measurement the catchment layer is built on, and the reason it can claim its
     * counts are exact: membership of a 15-minute catchment is decided by this number
     * for each real unit, not by whether a pin falls inside a drawn shape.
     *
     * **All the thresholds come from one call.** A row of durations answers 5, 10 and
     * 15 minutes at once, so asking per threshold would be three times the requests
     * for the same numbers.
     *
     * **The snap distance comes back with it, and it is not optional.** OSRM moves
     * every coordinate to the nearest routable way *however far that is*, and then
     * answers about the way rather than about the point. A grid point in the middle of
     * the Danube Delta is snapped ten kilometres to a road on the bank and reported as
     * a four-minute drive — which is how a fifteen-minute catchment came out at 1.596
     * km² of open water. The distance is the only thing that distinguishes a
     * measurement from that, so it is returned and the caller has to decide what it
     * will accept.
     *
     * `null` seconds means one of two different things and the caller must not conflate
     * them: OSRM returns null both for a point its graph cannot reach and for a point
     * with no way near enough to snap to at all. Either way it is *unmeasured*, which
     * is why it is passed through rather than turned into a large duration — a caller
     * that counted it as far away would be inventing a measurement, and one that
     * counted it as near would be inventing a business.
     *
     * A failure throws, unlike {@see self::reachable()}. That method is already on a
     * failure path and answers "assume reachable" to avoid a second one; here a
     * swallowed error would silently produce a catchment that is missing whatever the
     * failed chunk held, and store it as a fact.
     *
     * @param  array{float, float}  $from  latitude, longitude
     * @param  list<array{float, float}>  $to  latitude, longitude pairs
     * @param  'car'|'foot'  $profile
     * @return list<array{seconds: float|null, snap_metres: float|null}> one per destination, in the order given
     *
     * @throws RuntimeException when the router cannot be reached or answers a shape this does not recognise
     */
    public function table(array $from, array $to, string $profile = 'car'): array
    {
        if ($to == []) {
            return [];
        }

        $base = rtrim((string) config("services.osrm.profiles.{$profile}"), '/');

        if ($base == '') {
            throw new RuntimeException("No OSRM URL is configured for the {$profile} profile.");
        }

        // The source occupies one of the coordinates the table is allowed, so a
        // limit of 500 is 499 destinations. Off by one here is a `TooBig` on
        // every request, which is at least loud.
        $perRequest = max(1, (int) config('services.osrm.max_table_size') - 1);
        $durations = [];

        foreach (array_chunk($to, $perRequest) as $chunk) {
            $path = implode(';', array_map(
                fn (array $point): string => sprintf('%.6F,%.6F', $point[1], $point[0]),
                [$from, ...$chunk],
            ));

            try {
                $response = Http::timeout((int) config('services.osrm.timeout'))
                    ->get("{$base}/table/v1/driving/{$path}", ['sources' => 0, 'annotations' => 'duration']);
            } catch (Throwable $exception) {
                throw new RuntimeException("The {$profile} router did not answer: {$exception->getMessage()}", previous: $exception);
            }

            $row = $response->json('durations.0');
            $code = $response->json('code');

            if (! is_array($row)) {
                throw new RuntimeException(sprintf(
                    'The %s router answered %d with no duration row (%s).',
                    $profile,
                    $response->status(),
                    is_string($code) ? $code : 'no code',
                ));
            }

            /*
             * The row is the source to itself followed by the source to each
             * destination, so it is one longer than the chunk. Checked rather
             * than assumed: a row of the wrong length means the durations would
             * be attributed to the wrong units from here on, and every count
             * built on them would be plausible and wrong.
             */
            if (count($row) != count($chunk) + 1) {
                throw new RuntimeException(sprintf(
                    'The %s router returned %d durations for %d destinations.',
                    $profile, count($row) - 1, count($chunk),
                ));
            }

            /*
             * `destinations[]` is where OSRM says how far it had to move each
             * coordinate to find a road — the source is in `sources[]`, so this list
             * is one shorter than the duration row and lines up with the chunk.
             *
             * A response without it is not fatal. The snap distance is a filter the
             * caller applies, and a missing one means "unknown" rather than "zero":
             * reporting nought would tell the caller every point sat on a road.
             */
            $snapped = $response->json('destinations');
            $snapped = is_array($snapped) ? array_values($snapped) : [];

            foreach (array_slice(array_values($row), 1) as $index => $duration) {
                $metres = $snapped[$index]['distance'] ?? null;

                $durations[] = [
                    'seconds' => is_numeric($duration) ? (float) $duration : null,
                    'snap_metres' => is_numeric($metres) ? (float) $metres : null,
                ];
            }
        }

        return $durations;
    }

    /**
     * @param  array<string, mixed>|null  $body
     * @param  list<array{float, float}>  $stops
     */
    private function read(?array $body, array $stops, string $profile): RoutedPath
    {
        $code = $body['code'] ?? null;

        if ($code != 'Ok') {
            /*
             * `NoRoute` is a real answer and not a failure of ours: an island, or
             * a stop whose only access is a track the profile refuses. The caller
             * drops a stop and asks again.
             */
            throw new RuntimeException("The {$profile} router refused this route: ".(is_string($code) ? $code : 'no code'));
        }

        $route = $body['routes'][0] ?? null;
        $waypoints = $body['waypoints'] ?? [];

        if (! is_array($route) || ! is_string($route['geometry'] ?? null)) {
            throw new RuntimeException("The {$profile} router answered without a geometry.");
        }

        $legs = [];

        foreach ($route['legs'] ?? [] as $leg) {
            $legs[] = [
                'distance_m' => (int) round((float) ($leg['distance'] ?? 0)),
                'duration_s' => (int) round((float) ($leg['duration'] ?? 0)),
                'polyline' => $this->legPolyline($leg),
            ];
        }

        if (count($legs) != count($stops) - 1) {
            throw new RuntimeException(sprintf(
                'The %s router returned %d leg(s) for %d stops.', $profile, count($legs), count($stops),
            ));
        }

        $snapped = [];

        foreach ($waypoints as $index => $waypoint) {
            $location = $waypoint['location'] ?? null;

            $snapped[] = [
                /*
                 * OSRM reports this as the distance from the coordinate we asked
                 * for to the point it could route from. It is the only honest
                 * measure of whether a stop is reachable at all.
                 */
                'metres' => (int) round((float) ($waypoint['distance'] ?? 0)),
                'latitude' => is_array($location) ? (float) ($location[1] ?? $stops[$index][0]) : $stops[$index][0],
                'longitude' => is_array($location) ? (float) ($location[0] ?? $stops[$index][1]) : $stops[$index][1],
            ];
        }

        $path = new RoutedPath(
            distanceMetres: (int) round((float) ($route['distance'] ?? 0)),
            durationSeconds: (int) round((float) ($route['duration'] ?? 0)),
            polyline: $route['geometry'],
            legs: $legs,
            snapped: $snapped,
        );

        $this->verifyGeometry($path, $profile);

        return $path;
    }

    /**
     * One leg's turns joined into one line.
     *
     * OSRM gives a leg no geometry of its own: it gives the whole route one line
     * and each turn inside the leg another, so a leg is the turns of that leg,
     * joined. Consecutive turns share the coordinate they meet at, and the final
     * "arrive" turn is that coordinate twice, so a point identical to the one
     * before it is dropped — one rule that covers both, rather than two rules
     * about where OSRM puts its duplicates.
     */
    private function legPolyline(mixed $leg): string
    {
        $points = [];

        foreach ((is_array($leg) ? $leg['steps'] ?? [] : []) as $step) {
            if (! is_array($step) || ! is_string($step['geometry'] ?? null)) {
                continue;
            }

            foreach (Polyline6::decode($step['geometry']) as $point) {
                if ($points != [] && $points[count($points) - 1] == $point) {
                    continue;
                }

                $points[] = $point;
            }
        }

        return $points == [] ? '' : Polyline6::encode($points);
    }

    /**
     * Check that the line we stored starts where the router said it starts.
     *
     * The one guard against the failure this whole class is shaped around: a
     * transposed coordinate pair produces a route that is valid in every field a
     * database could check. Comparing the decoded first point with OSRM's own
     * first snapped waypoint catches it, and catches a decoder that drifts, for
     * the cost of decoding one line.
     */
    private function verifyGeometry(RoutedPath $path, string $profile): void
    {
        $points = Polyline6::decode($path->polyline);

        if ($points == [] || $path->snapped == []) {
            throw new RuntimeException("The {$profile} router returned an empty line.");
        }

        [$latitude, $longitude] = $points[0];
        $first = $path->snapped[0];

        $metres = $this->metresBetween($latitude, $longitude, $first['latitude'], $first['longitude']);

        if ($metres > self::GEOMETRY_TOLERANCE_METRES) {
            throw new RuntimeException(sprintf(
                'The %s route begins %d m from its own first waypoint, so the geometry cannot be trusted.',
                $profile, $metres,
            ));
        }

        $this->verifyLegs($path, $profile);

        Log::debug('Routed a path.', [
            'profile' => $profile,
            'stops' => count($path->snapped),
            'metres' => $path->distanceMetres,
            'worst_snap_m' => $path->worstSnapMetres(),
        ]);
    }

    /**
     * Check that each leg's line starts at the stop the leg starts from.
     *
     * A leg drawn from the wrong end, or the legs assembled one out of order, is a
     * route that looks right in every number and wrong on the map — and nobody
     * would find it in a database. Leg *n* begins at snapped waypoint *n*, so that
     * is what it is held to, at the cost of decoding a few short lines during a
     * batch that runs once.
     */
    private function verifyLegs(RoutedPath $path, string $profile): void
    {
        foreach ($path->legs as $index => $leg) {
            $polyline = (string) ($leg['polyline'] ?? '');

            if ($polyline == '') {
                throw new RuntimeException(sprintf(
                    'The %s router returned no line for leg %d of %d.', $profile, $index + 1, count($path->legs),
                ));
            }

            $points = Polyline6::decode($polyline);
            $from = $path->snapped[$index] ?? null;

            if ($points == [] || $from === null) {
                throw new RuntimeException(sprintf('The %s route cannot place leg %d.', $profile, $index + 1));
            }

            $metres = $this->metresBetween($points[0][0], $points[0][1], $from['latitude'], $from['longitude']);

            if ($metres > self::GEOMETRY_TOLERANCE_METRES) {
                throw new RuntimeException(sprintf(
                    'Leg %d of the %s route begins %d m from the stop it leaves.', $index + 1, $profile, $metres,
                ));
            }
        }
    }

    private function metresBetween(float $fromLat, float $fromLng, float $toLat, float $toLng): int
    {
        $deltaLat = deg2rad($toLat - $fromLat);
        $deltaLng = deg2rad($toLng - $fromLng);

        $a = sin($deltaLat / 2) ** 2
            + cos(deg2rad($fromLat)) * cos(deg2rad($toLat)) * sin($deltaLng / 2) ** 2;

        return (int) round(6_371_000.0 * 2 * atan2(sqrt($a), sqrt(1 - $a)));
    }
}
