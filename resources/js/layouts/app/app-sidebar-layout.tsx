import type { PropsWithChildren } from 'react';

import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { cn } from '@/lib/utils';
import type { BreadcrumbItem } from '@/types';

export default function AppSidebarLayout({
    children,
    breadcrumbs = [],
    fullHeight = false,
}: PropsWithChildren<{ breadcrumbs?: BreadcrumbItem[]; fullHeight?: boolean }>) {
    return (
        <AppShell variant="sidebar">
            <AppSidebar />
            <AppContent
                variant="sidebar"
                className={cn(
                    'overflow-x-hidden',
                    /*
                     * Pages scroll as a whole by default, which is right for
                     * almost all of them. A page that wants a panel of its own
                     * to scroll — a conversation, a chat — needs a definite
                     * height to size that panel against, and the shell only
                     * ever sets a minimum. This gives it one.
                     *
                     * From `lg` up only: on a phone, two nested scroll areas
                     * are worse than the long page they replace.
                     */
                    fullHeight && 'lg:h-svh lg:overflow-hidden',
                )}
            >
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                {children}
            </AppContent>
        </AppShell>
    );
}
