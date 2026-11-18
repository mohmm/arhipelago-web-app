<?php

use App\Services\Routing\Polyline6;

/*
 * Decoding the line OSRM returns.
 *
 * The fixture is a real answer from the local router — a 1.362 m route out of
 * Bran — kept together with the two waypoints OSRM said it snapped to. That
 * pairing is what makes the assertion worth anything: the expected coordinates
 * come from the router's own `waypoints[].location`, computed by something other
 * than the code under test, so a decoder that drifts cannot agree with them.
 *
 * This runs in the Unit suite, which does not boot the application, so nothing
 * here may reach for a container or a config value.
 */

/** `GET :5001/route/v1/driving/25.3690,45.5140;25.3750,45.5180?geometries=polyline6` */
const ROUTE = 'cp}xuAqmgko@aGp@wJpEeZj^mLdFePqEm_@yRyNoK{HaJqEwM_CqSdAyfAdCu\\dQe_A}AeMmIaKoJ}EuP[uMrCgx@hYsJpAeKeDeVm\\iFoEqFgAmAif@zLa}@lBch@oC{n@{Jy|@E{NfDkWfChA|M`UzUlK';

it('decodes a real route to the points the router snapped to', function () {
    $points = Polyline6::decode(ROUTE);

    expect($points)->toHaveCount(35);

    // OSRM's own waypoints: [25.366761, 45.514002] and [25.374822, 45.518042],
    // written lng first as everything in that API is.
    [$firstLat, $firstLng] = $points[0];
    [$lastLat, $lastLng] = $points[count($points) - 1];

    expect(round($firstLat, 6))->toBe(45.514002)
        ->and(round($firstLng, 6))->toBe(25.366761)
        ->and(round($lastLat, 6))->toBe(45.518042)
        ->and(round($lastLng, 6))->toBe(25.374822);
});

it('keeps every point inside the country it was measured in', function () {
    /*
     * A precision mistake — decoding polyline6 as polyline5 — produces
     * coordinates ten times too large rather than an error, and a route in the
     * Indian Ocean looks exactly like a route until somebody draws it.
     */
    foreach (Polyline6::decode(ROUTE) as [$latitude, $longitude]) {
        expect($latitude)->toBeGreaterThan(43.0)->toBeLessThan(48.5)
            ->and($longitude)->toBeGreaterThan(20.0)->toBeLessThan(31.0);
    }
});

it('returns what it managed to read from a truncated line', function () {
    /*
     * Half a line draws as half a route. Throwing would turn one bad row into a
     * page that renders nothing at all, and the caller checks the first point
     * against a waypoint it already knows.
     */
    $whole = Polyline6::decode(ROUTE);
    $half = Polyline6::decode(substr(ROUTE, 0, 40));

    expect($half)->not->toBe([])
        ->and(count($half))->toBeLessThan(count($whole))
        ->and($half[0])->toBe($whole[0]);
});

it('reads an empty line as no points', function () {
    expect(Polyline6::decode(''))->toBe([]);
});

it('re-encodes a real route to the exact string it came from', function () {
    /*
     * The strongest thing that can be said about an encoder: hand it what the
     * router wrote, and get the router's own bytes back. Every value in between is
     * an integer divided by a million and multiplied back, so the round trip is
     * exact rather than approximate — and one wrong bit in the zigzag or the
     * five-bit split would change the string.
     */
    expect(Polyline6::encode(Polyline6::decode(ROUTE)))->toBe(ROUTE);
});

it('encodes westward and southward steps, where the sign bit is', function () {
    // The half of the zigzag an encoder gets wrong: a negative difference has to
    // come out as a short string, not as a very large positive one.
    $points = [[45.5, 25.4], [45.4, 25.3], [45.3, 25.2]];

    $decoded = Polyline6::decode(Polyline6::encode($points));

    expect($decoded)->toHaveCount(3);

    foreach ($points as $index => [$latitude, $longitude]) {
        expect(round($decoded[$index][0], 6))->toBe($latitude)
            ->and(round($decoded[$index][1], 6))->toBe($longitude);
    }
});

it('encodes no points as an empty line', function () {
    expect(Polyline6::encode([]))->toBe('');
});

it('decodes negative offsets, which every westward step is', function () {
    /*
     * The zigzag is where an encoder and a decoder most often disagree: the sign
     * lives in the low bit, so getting it wrong sends every second point
     * backwards. This line runs west and south from its start.
     */
    $points = Polyline6::decode('_c`|hB_ovqcA~oB~oB');

    expect($points)->toHaveCount(2)
        ->and($points[1][0])->toBeLessThan($points[0][0])
        ->and($points[1][1])->toBeLessThan($points[0][1]);
});
