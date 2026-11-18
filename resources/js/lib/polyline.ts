/*
 * Google's encoded-polyline format at six decimal places.
 *
 * OSRM answers with this and the route is stored exactly as it arrived: a real
 * five-stop route through Bran is 1.317 points, about 26 KB written out as
 * coordinate pairs and about 5 KB encoded. The saving is not the disk, it is what
 * the page has to carry.
 *
 * Forty lines rather than a dependency. `@mapbox/polyline` does this and nothing
 * else, and a package in the bundle is a package to audit, update and trust; the
 * format is a published specification that has not changed since 2010. The PHP
 * side has its own copy, for a different job — it decodes the line to check that
 * a route begins where the router said it does, which is the one way a transposed
 * coordinate pair can be caught.
 */

/** The number of points beyond which a line is thinned before drawing. */
const SMOOTH_ABOVE = 1200;

/**
 * Decode a polyline6 string into latitude/longitude pairs.
 *
 * Each value is a difference from the previous one, zigzagged so a negative
 * number encodes as a small positive one, split into five-bit groups with a
 * continuation bit, and offset by 63 into printable ASCII.
 *
 * A malformed string returns the points it managed to read. The alternative is a
 * page that throws while rendering a map, and a truncated line draws as a
 * shorter route rather than as a blank panel.
 */
export function decodePolyline6(encoded: string): [number, number][] {
    const points: [number, number][] = [];
    let index = 0;
    let latitude = 0;
    let longitude = 0;

    while (index < encoded.length) {
        const deltas: number[] = [];

        for (let axis = 0; axis < 2; axis++) {
            let shift = 0;
            let result = 0;
            let byte = 0;

            do {
                if (index >= encoded.length) {
                    return points;
                }

                byte = encoded.charCodeAt(index++) - 63;
                result |= (byte & 0x1f) << shift;
                shift += 5;
            } while (byte >= 0x20);

            // The zigzag: the low bit carries the sign.
            deltas.push(result & 1 ? ~(result >> 1) : result >> 1);
        }

        latitude += deltas[0];
        longitude += deltas[1];

        points.push([latitude / 1e6, longitude / 1e6]);
    }

    return points;
}

/**
 * Every nth point, when there are more than a screen can show.
 *
 * A 1.300-point line is 1.300 SVG segments for a route that is 40 px wide at the
 * zoom a whole commune fits into. Dropping points changes nothing a reader can
 * see and takes the redraw on a pan from noticeable to instant. The last point is
 * always kept, so a thinned line still ends where the route ends.
 */
export function thin(points: [number, number][], limit = SMOOTH_ABOVE): [number, number][] {
    if (points.length <= limit) {
        return points;
    }

    const step = Math.ceil(points.length / limit);
    const thinned = points.filter((_, index) => index % step === 0);
    const last = points[points.length - 1];

    if (thinned[thinned.length - 1] !== last) {
        thinned.push(last);
    }

    return thinned;
}
