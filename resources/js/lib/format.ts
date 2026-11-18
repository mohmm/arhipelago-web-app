/**
 * Numbers, dates, distances and durations, formatted the same way on every screen.
 */

/**
 * A plain whole number with the reader's own thousands separator, so `16000`
 * reads as `16.000` to a Romanian and `16,000` to an English speaker.
 */
export function formatCount(value: number, locale: string): string {
    return new Intl.NumberFormat(locale, { maximumFractionDigits: 0 }).format(value);
}

/** An em dash rather than an empty cell, so a missing date still occupies its column. */
export function formatDate(value: string | null | undefined, locale: string): string {
    return value == null ? '—' : new Date(value).toLocaleDateString(locale, { dateStyle: 'medium' });
}

export function formatDateTime(value: string | null | undefined, locale: string): string {
    return value == null ? '—' : new Date(value).toLocaleString(locale, { dateStyle: 'medium', timeStyle: 'short' });
}

/**
 * A distance the way the app prints it: "510 m" under a kilometre, "28,4 km" above.
 */
export function formatDistance(metres: number, locale: string): string {
    if (metres < 1000) {
        return `${Math.round(metres)} m`;
    }

    return `${new Intl.NumberFormat(locale, { maximumFractionDigits: 1 }).format(metres / 1000)} km`;
}

/**
 * A duration the way the app prints it: "55 min", or "1 h 5 min" from an hour up.
 */
export function formatDuration(seconds: number): string {
    const minutes = Math.max(0, Math.round(seconds / 60));

    if (minutes < 60) {
        return `${minutes} min`;
    }

    const rest = minutes % 60;

    return rest == 0 ? `${Math.floor(minutes / 60)} h` : `${Math.floor(minutes / 60)} h ${rest} min`;
}
