import { Link } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { ChevronLeft, ChevronRight, MoreHorizontal } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { formatCount } from '@/lib/format';
import type { PaginationLink } from '@/types';

interface Props {
    meta: {
        current_page: number;
        last_page: number;
        from: number | null;
        to: number | null;
        total: number;
    };
    links: PaginationLink[];
    /**
     * The props a page turn should ask for, where asking for all of them is wasteful.
     *
     * A table beside a deferred map is the case: the outlines are the same on page two,
     * they are half a megabyte of coordinates, and a full visit both re-fetches them and
     * drops the map back into its skeleton. Left out, every page keeps today's behaviour.
     */
    only?: string[];
}

export default function TablePagination({ meta, links, only }: Props) {
    const { tChoice, currentLocale } = useLaravelReactI18n();
    const locale = currentLocale() ?? 'ro';

    if (meta.last_page <= 1) {
        return null;
    }

    const { current_page: currentPage, last_page: lastPage } = meta;
    const prevLink = links[0];
    const nextLink = links[links.length - 1];
    const pageLinks = links.slice(1, -1);
    const visiblePages = getVisiblePages(currentPage, lastPage);

    return (
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            {/* One sentence rather than four words stitched together: the noun agrees
                with the total in Romanian („19 rezultate", „24 de rezultate"), and the
                word order belongs to the translation. */}
            <div className="hidden text-sm text-muted-foreground tabular-nums sm:block">
                {tChoice('Showing :first–:last of :total result|Showing :first–:last of :total results', meta.total, {
                    first: formatCount(meta.from ?? 0, locale),
                    last: formatCount(meta.to ?? 0, locale),
                    total: formatCount(meta.total, locale),
                })}
            </div>

            <div className="flex items-center justify-between gap-2 sm:justify-end">
                <NavButton link={prevLink} direction="prev" only={only} />

                <div className="hidden items-center gap-1 md:flex">
                    {visiblePages.map((page, idx) =>
                        page === 'ellipsis' ? (
                            <span key={`ellipsis-${idx}`} className="flex h-9 w-9 items-center justify-center">
                                <MoreHorizontal className="h-4 w-4 text-muted-foreground" />
                            </span>
                        ) : (
                            <PageButton key={page} page={page} link={pageLinks[page - 1]} isActive={currentPage === page} only={only} />
                        ),
                    )}
                </div>

                <span className="text-sm text-muted-foreground md:hidden">
                    {currentPage} / {lastPage}
                </span>

                <NavButton link={nextLink} direction="next" only={only} />
            </div>
        </div>
    );
}

function NavButton({ link, direction, only }: { link: PaginationLink | undefined; direction: 'prev' | 'next'; only?: string[] }) {
    const { t } = useLaravelReactI18n();
    const label = direction === 'prev' ? t('Previous') : t('Next');

    return (
        <Button variant="outline" size="sm" className="gap-1" disabled={!link?.url} asChild={!!link?.url}>
            {link?.url ? (
                <Link href={link.url} preserveState preserveScroll only={only}>
                    {direction === 'prev' && <ChevronLeft className="h-4 w-4" />}
                    <span className="sm:inline">{label}</span>
                    {direction === 'next' && <ChevronRight className="h-4 w-4" />}
                </Link>
            ) : (
                <span className="inline-flex items-center gap-1">
                    {direction === 'prev' && <ChevronLeft className="h-4 w-4" />}
                    <span className="sm:inline">{label}</span>
                    {direction === 'next' && <ChevronRight className="h-4 w-4" />}
                </span>
            )}
        </Button>
    );
}

function PageButton({ page, link, isActive, only }: { page: number; link: PaginationLink | undefined; isActive: boolean; only?: string[] }) {
    if (!link) {
        return null;
    }

    return (
        <Button
            variant={isActive ? 'outline' : 'ghost'}
            size="sm"
            className="h-9 w-9 p-0 text-sm"
            disabled={!link.url}
            asChild={!!link.url && !isActive}
        >
            {link.url && !isActive ? (
                <Link href={link.url} preserveState preserveScroll only={only}>
                    {page}
                </Link>
            ) : (
                <span>{page}</span>
            )}
        </Button>
    );
}

function getVisiblePages(current: number, last: number): (number | 'ellipsis')[] {
    if (last <= 7) {
        return Array.from({ length: last }, (_, i) => i + 1);
    }

    const pages: (number | 'ellipsis')[] = [1];

    if (current > 3) {
        pages.push('ellipsis');
    }

    const start = Math.max(2, current - 1);
    const end = Math.min(last - 1, current + 1);

    for (let i = start; i <= end; i++) {
        pages.push(i);
    }

    if (current < last - 2) {
        pages.push('ellipsis');
    }

    pages.push(last);

    return pages;
}
