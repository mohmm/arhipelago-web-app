import { Link } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import AppLogoIcon from '@/components/app-logo-icon';
import LanguageSwitcher from '@/components/language-switcher';
import { useLocalizedUrl } from '@/lib/locale';

interface AuthBrandLayoutProps {
    title: string;
    description?: string;
}

/**
 * The sign-in screens, drawn like the app's: the language at the top, the
 * logo and name, a large title, and the form below.
 */
export default function AuthBrandLayout({ children, title, description }: PropsWithChildren<AuthBrandLayoutProps>) {
    const url = useLocalizedUrl();

    return (
        <div className="flex min-h-svh items-center justify-center bg-muted/40 p-4 sm:p-8">
            <div className="w-full max-w-md rounded-2xl border bg-card p-6 shadow-sm sm:p-10">
                <div className="flex justify-end">
                    <LanguageSwitcher />
                </div>

                <Link href={url('/')} className="mt-2 inline-flex items-center gap-3">
                    <AppLogoIcon className="size-11 rounded-xl object-contain" />
                    <span className="text-2xl font-semibold tracking-tight">Smartsense</span>
                </Link>

                <div className="mt-8 mb-6 space-y-1.5">
                    <h1 className="text-3xl font-bold tracking-tight">{title}</h1>
                    {description && <p className="text-muted-foreground">{description}</p>}
                </div>

                {children}
            </div>
        </div>
    );
}
