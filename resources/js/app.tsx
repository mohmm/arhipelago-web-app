import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import type { ResolvedComponent } from '@inertiajs/react';
import { LaravelReactI18nProvider } from 'laravel-react-i18n';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { Toaster } from '@/components/ui/sonner';
import { listenForServerToasts } from '@/lib/toast';
import { initializeTheme } from './hooks/use-appearance';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => title ? `${title} - ${appName}` : appName,
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.tsx`,
            import.meta.glob<{ default: ResolvedComponent }>('./pages/**/*.tsx'),
        ).then((module) => module.default),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <LaravelReactI18nProvider
              locale={document.documentElement.lang}
              fallbackLocale="en"
              // Eager on purpose. Lazily, the provider fetches the locale and
              // the fallback as two concurrent dynamic imports after first
              // paint, and re-renders on whichever resolves first — the second
              // one's `setLoading(false)` is a no-op, so React bails out and
              // never renders again. `en.json` is the smaller file, so it won
              // that race consistently and left every Romanian page in English
              // until a navigation forced a re-render. Eagerly, both files are
              // in the bundle and the map is filled during the first render.
              files={import.meta.glob('/lang/*.json', { eager: true })}
            >
              <App {...props} />
              <Toaster />
            </LaravelReactI18nProvider>
          );
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on load...
initializeTheme();

listenForServerToasts();
