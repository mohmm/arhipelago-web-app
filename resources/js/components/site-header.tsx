import { Link, usePage } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import AppLogo from '@/components/app-logo';
import AppearanceToggleDropdown from '@/components/appearance-dropdown';
import LanguageSwitcher from '@/components/language-switcher';
import { Button } from '@/components/ui/button';
import { useLocalizedUrl } from '@/lib/locale';
import type { SharedData } from '@/types';

/**
 * The website's header: the logo, the language, the theme and the way into the
 * admin panel. A phone has no room for the language and the theme here; the
 * footer carries both on every page.
 */
export function SiteHeader() {
    const { t } = useLaravelReactI18n();
    const url = useLocalizedUrl();
    const { auth } = usePage<SharedData>().props;

    return (
        <header className="sticky top-0 z-40 border-b bg-background/90 backdrop-blur">
            <div className="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6">
                <Link href={url('/')} className="flex items-center gap-2.5">
                    <AppLogo />
                </Link>
                <div className="flex items-center gap-3">
                    <div className="hidden items-center gap-2 sm:flex">
                        <LanguageSwitcher />
                        <AppearanceToggleDropdown />
                    </div>
                    <Button asChild variant="outline" size="sm">
                        <Link href={auth.user ? url('/admin/dashboard') : url('/login')}>{auth.user ? t('Admin panel') : t('Sign in')}</Link>
                    </Button>
                </div>
            </div>
        </header>
    );
}
