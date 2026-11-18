import { useLaravelReactI18n } from 'laravel-react-i18n';

/**
 * Send a request to the same locale the person is reading.
 *
 * Routes are registered behind a locale prefix, and the app is configured to
 * fall back to the browser's `Accept-Language` header when a request arrives
 * without one. A `POST` to an unprefixed path therefore runs under whatever
 * language Chrome happens to advertise — so somebody working in Romanian
 * presses a button and gets an English answer back, on a page that is
 * otherwise entirely Romanian.
 *
 * `GET`s are already safe: an unprefixed one is redirected to the prefixed URL
 * before it reaches a controller. The redirect middleware deliberately ignores
 * the other verbs — a 302 would turn a `POST` into a `GET` and lose the body —
 * so a mutating request is the one shape that arrives with nothing to read the
 * language from.
 *
 * Prefixing the path fixes it at the only place that knows the answer: the
 * page doing the asking. Not the session, which is shared by every tab and
 * would answer an English one in Romanian because a Romanian one loaded last.
 */
export function localized(path: string, locale: string): string {
    if (locale === '' || path === `/${locale}` || path.startsWith(`/${locale}/`)) {
        return path;
    }

    return `/${locale}${path}`;
}

/**
 * A path prefixer bound to the language the page is written in.
 *
 * Every mutating request in the application goes through this, including the
 * `action` of a Wayfinder `<Form>` — those URLs are written at build time by an
 * Artisan command that has no request to read a locale from, so they all come
 * out unprefixed.
 */
export function useLocalizedUrl(): (path: string) => string {
    const { currentLocale } = useLaravelReactI18n();
    // Never null in practice — the provider is seeded from `<html lang>` — but
    // an empty locale leaves the path alone rather than inventing a language.
    const locale = currentLocale() ?? '';

    return (path: string) => localized(path, locale);
}
