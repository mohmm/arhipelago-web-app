import { useSyncExternalStore } from 'react';

const MOBILE_BREAKPOINT = 768;
const QUERY = `(max-width: ${MOBILE_BREAKPOINT - 1}px)`;

function subscribe(onChange: () => void): () => void {
    const query = window.matchMedia(QUERY);
    query.addEventListener('change', onChange);

    return () => query.removeEventListener('change', onChange);
}

/** Whether the window is phone-sized, kept up to date as it is resized. */
export function useIsMobile(): boolean {
    return useSyncExternalStore(subscribe, () => window.matchMedia(QUERY).matches, () => false);
}
