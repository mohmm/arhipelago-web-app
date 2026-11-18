import { Link, usePage } from '@inertiajs/react';
import { cn } from '@/lib/utils';

export type SectionTab = { title: string; href: string };

/**
 * The pages of one admin section side by side, such as Posts, Comments and
 * Reviews under Community.
 */
export default function SectionTabs({ tabs }: { tabs: SectionTab[] }) {
    const path = usePage().url.split('?')[0];

    return (
        <nav className="flex w-fit gap-1 rounded-xl bg-muted p-1">
            {tabs.map((tab) => {
                const active = path == tab.href || path.startsWith(`${tab.href}/`);

                return (
                    <Link
                        key={tab.href}
                        href={tab.href}
                        prefetch
                        className={cn(
                            'rounded-lg px-4 py-1.5 text-sm font-medium transition-colors',
                            active ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground',
                        )}
                    >
                        {tab.title}
                    </Link>
                );
            })}
        </nav>
    );
}
