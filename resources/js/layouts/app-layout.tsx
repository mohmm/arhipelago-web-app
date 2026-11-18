import type { ReactNode } from 'react';
import { VerifyEmailBanner } from '@/components/verify-email-banner';
import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import type { BreadcrumbItem } from '@/types';

interface AppLayoutProps {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
    /**
     * Bound the page to the viewport so a panel inside it can scroll on its
     * own. Opt-in, because it costs the page its natural scroll.
     */
    fullHeight?: boolean;
}

export default ({ children, breadcrumbs, fullHeight, ...props }: AppLayoutProps) => (
    <AppLayoutTemplate breadcrumbs={breadcrumbs} fullHeight={fullHeight} {...props}>
        {/* Here rather than at the app root: it reads page props, which only exist inside the page tree. */}
        {/* `empty:hidden` so a verified user does not get a strip of padding
            where the banner would have been. */}
        <div className="px-4 pt-4 empty:hidden">
            <VerifyEmailBanner />
        </div>
        {children}
    </AppLayoutTemplate>
);
