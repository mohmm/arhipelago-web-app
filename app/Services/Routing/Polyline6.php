<?php

namespace App\Services\Routing;

/**
 * Google's encoded-polyline format at six decimal places, decoded.
 *
 * OSRM answers with this when asked for `geometries=polyline6`, and it is what
 * gets stored: a real five-stop route through Bran is 1.317 points, about 26 KB
 * as a list of coordinate pairs and about 5 KB encoded. The saving is not the
 * disk, it is the 3.180 communes × 5 themes of it that a page has to carry.
 *
 * Decoding here is not for rendering — the browser does that, from the same
 * string — but for checking. A coordinate pair sent to OSRM in the wrong order
 * comes back as a perfectly valid route through Kazakhstan, with a distance and
 * a duration that look reasonable in a database column. Decoding the first point
 * and comparing it to the first waypoint is what turns that into a failed import
 * rather than a published itinerary.
 *
 * Encoding is here for one reason: a route's legs arrive from OSRM as one
 * encoded string per turn, and the format is delta-based, so two encoded strings
 * cannot be joined by concatenating them — the second one's first point would be
 * read as an offset from the first one's last. Joining a leg's turns into a leg
 * means decoding them, appending the points, and writing the line back out.
 */
class Polyline6
{
    /** Six decimal places: roughly 11 cm at the equator. */
    private const FACTOR = 1_000_000;

    /**
     * Decode a polyline into latitude/longitude pairs.
     *
     * Values are stored as differences from the previous point, each zigzagged
     * so a negative number encodes as a small positive one, then split into
     * five-bit groups with the continuation bit set on every group but the last,
     * and offset by 63 to land in printable ASCII.
     *
     * A malformed string yields the points it managed to read rather than an
     * exception. It is checked by the caller against a waypoint it already
     * knows, so a truncated line fails that comparison — and a decoder that
     * throws would turn one bad route into a failed batch of three hundred.
     *
     * @return list<array{float, float}>
     */
    public static function decode(string $encoded): array
    {
        $points = [];
        $index = 0;
        $length = strlen($encoded);
        $latitude = 0;
        $longitude = 0;

        while ($index < $length) {
            foreach (['latitude', 'longitude'] as $axis) {
                $shift = 0;
                $result = 0;

                do {
                    if ($index >= $length) {
                        return $points;
                    }

                    $byte = ord($encoded[$index++]) - 63;
                    $result |= ($byte & 0x1F) << $shift;
                    $shift += 5;
                } while ($byte >= 0x20);

                // The zigzag: the low bit carries the sign.
                $delta = ($result & 1) !== 0 ? ~($result >> 1) : ($result >> 1);

                $axis == 'latitude' ? $latitude += $delta : $longitude += $delta;
            }

            $points[] = [$latitude / self::FACTOR, $longitude / self::FACTOR];
        }

        return $points;
    }

    /**
     * Encode latitude/longitude pairs into a polyline.
     *
     * The exact inverse of the above, and exact in the arithmetic sense too:
     * every value that came out of `decode()` was an integer divided by a million,
     * so multiplying it back and rounding returns the integer it started as. A
     * line decoded and re-encoded is the same string.
     *
     * @param  list<array{float, float}>  $points
     */
    public static function encode(array $points): string
    {
        $encoded = '';
        $latitude = 0;
        $longitude = 0;

        foreach ($points as $point) {
            $nextLatitude = (int) round($point[0] * self::FACTOR);
            $nextLongitude = (int) round($point[1] * self::FACTOR);

            $encoded .= self::chunk($nextLatitude - $latitude).self::chunk($nextLongitude - $longitude);

            $latitude = $nextLatitude;
            $longitude = $nextLongitude;
        }

        return $encoded;
    }

    /**
     * One difference, zigzagged and split into five-bit groups.
     */
    private static function chunk(int $delta): string
    {
        // The zigzag the decoder undoes: doubled, and every bit flipped when
        // negative, so the sign ends up in the low bit and small negative numbers
        // encode as short strings rather than as very large positive ones.
        $value = $delta < 0 ? ~($delta << 1) : $delta << 1;
        $chunk = '';

        while ($value >= 0x20) {
            $chunk .= chr((0x20 | ($value & 0x1F)) + 63);
            $value >>= 5;
        }

        return $chunk.chr($value + 63);
    }
}
