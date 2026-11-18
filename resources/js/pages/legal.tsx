import { Head, Link, usePage } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { CalendarDays, FileText, Mail } from 'lucide-react';

import { Badge } from '@/components/ui/badge';
import WebsiteLayout from '@/layouts/website-layout';
import { cn } from '@/lib/utils';
import type { SharedData } from '@/types';

type LegalDocument = {
    slug: string;
    title: string;
    /**
     * Rendered from Markdown on the server, with HTML stripped from the source
     * before it ever reaches here. That is what makes injecting it safe: the
     * only markup present is markup CommonMark produced.
     */
    content_html: string;
    /** The document's own sections, anchored to headings in `content_html`. */
    sections: { id: string; title: string }[];
    version: string;
    effective_date: string | null;
    updated_at: string | null;
};

type Props = {
    document: LegalDocument;
    /** Every published document, so one can be read after another. */
    documents: { slug: string; title: string; url: string }[];
};

export default function Legal({ document, documents }: Props) {
    const { t, currentLocale } = useLaravelReactI18n();
    const { company } = usePage<SharedData>().props;

    function formatDate(value: string) {
        return new Date(value).toLocaleDateString(currentLocale() ?? 'ro', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    }

    return (
        <WebsiteLayout>
            <Head title={document.title} />

            <section className="relative isolate overflow-hidden border-b border-border">
                <div
                    className="absolute inset-0 -z-10 bg-grid [mask-image:radial-gradient(ellipse_70%_60%_at_50%_0%,black,transparent)] opacity-40"
                    aria-hidden="true"
                />
                <div
                    className="absolute top-[-14rem] left-1/2 -z-10 h-96 w-[40rem] -translate-x-1/2 rounded-full bg-brand/10 blur-[120px]"
                    aria-hidden="true"
                />

                <div className="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                    <Badge variant="brandSubtle">{t('Legal')}</Badge>

                    <h1 className="mt-5 max-w-3xl text-3xl font-bold tracking-tight text-balance sm:text-4xl">{document.title}</h1>

                    <div className="mt-5 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-muted-foreground">
                        <span className="flex items-center gap-2">
                            <FileText className="h-4 w-4" />
                            {t('Version :version', { version: document.version })}
                        </span>
                        {document.effective_date && (
                            <span className="flex items-center gap-2">
                                <CalendarDays className="h-4 w-4" />
                                {t('In force from :date', { date: formatDate(document.effective_date) })}
                            </span>
                        )}
                    </div>
                </div>
            </section>

            <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
                {/*
                 * Two columns rather than one centred one: navigation on the left,
                 * the document filling everything left over. These run to five
                 * thousand words across twenty sections, so somewhere to jump from
                 * matters more than the margins a centred column would have kept.
                 */}
                <div className="lg:grid lg:grid-cols-[18rem_minmax(0,1fr)] lg:gap-12 xl:gap-16">
                    <aside className="mb-10 lg:mb-0">
                        <div className="lg:sticky lg:top-24">
                            <nav aria-label={t('Legal')}>
                                <h2 className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">{t('Documents')}</h2>
                                <ul className="mt-3 space-y-0.5">
                                    {documents.map((item) => {
                                        const isCurrent = item.slug === document.slug;

                                        return (
                                            <li key={item.slug}>
                                                <Link
                                                    href={item.url}
                                                    aria-current={isCurrent ? 'page' : undefined}
                                                    className={cn(
                                                        'block rounded-md px-3 py-1.5 text-sm transition-colors',
                                                        isCurrent
                                                            ? 'bg-brand-subtle font-medium text-brand-accent'
                                                            : 'text-muted-foreground hover:bg-accent hover:text-foreground',
                                                    )}
                                                >
                                                    {item.title}
                                                </Link>
                                            </li>
                                        );
                                    })}
                                </ul>
                            </nav>

                            {document.sections.length > 1 && (
                                <nav aria-label={t('On this page')} className="mt-8 hidden lg:block">
                                    <h2 className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">{t('On this page')}</h2>
                                    {/* Capped and scrollable: the DPA has twenty sections, which
                                        would otherwise run off the bottom of a sticky rail. */}
                                    <ul className="mt-3 max-h-[calc(100svh-20rem)] space-y-0.5 overflow-y-auto pr-2">
                                        {document.sections.map((section) => (
                                            <li key={section.id}>
                                                <a
                                                    href={`#${section.id}`}
                                                    className="block rounded-md px-3 py-1.5 text-sm text-muted-foreground transition-colors hover:bg-accent hover:text-foreground"
                                                >
                                                    {section.title}
                                                </a>
                                            </li>
                                        ))}
                                    </ul>
                                </nav>
                            )}
                        </div>
                    </aside>

                    <div className="min-w-0">
                        <article
                            className={cn(
                                /*
                                 * The whole document uses the width the column gives it —
                                 * text and tables alike.
                                 *
                                 * That puts a line at around 100 characters, past the 45–75
                                 * that reads most comfortably, so the type steps up to 18px
                                 * from `lg` where the line is long. Bigger glyphs mean fewer
                                 * of them per line and a taller line box to track back along,
                                 * which is what keeps a long measure legible.
                                 */
                                'prose max-w-none prose-neutral lg:prose-lg dark:prose-invert',
                                'prose-headings:scroll-mt-24 prose-headings:font-semibold prose-headings:tracking-tight',
                                'prose-h2:mt-12 prose-h2:mb-4 prose-h2:text-xl prose-h3:mt-8 prose-h3:text-base',
                                // The spacing above a heading separates it from the section
                                // before it. The first one has nothing before it, so the gap
                                // just leaves the document opening halfway down the screen.
                                '[&>:first-child]:mt-0',
                                'prose-p:leading-relaxed prose-li:my-1',
                                'prose-a:font-medium prose-a:text-brand-accent prose-a:no-underline hover:prose-a:underline',
                                'prose-strong:text-foreground',
                                // Still scrollable, for the narrow screens where even the
                                // full width is not enough.
                                'prose-table:block prose-table:w-full prose-table:overflow-x-auto prose-table:text-sm',
                                '[&_td]:align-top [&_th]:align-top',
                            )}
                            dangerouslySetInnerHTML={{ __html: document.content_html }}
                        />

                        <div className="mt-14 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-border bg-muted/40 px-6 py-5 text-sm">
                            {document.updated_at ? (
                                <p className="text-muted-foreground">{t('Last updated on :date.', { date: formatDate(document.updated_at) })}</p>
                            ) : (
                                <span />
                            )}

                            <a
                                href={`mailto:${company.email}`}
                                className="flex items-center gap-2 font-medium transition-colors hover:text-brand-accent"
                            >
                                <Mail className="h-4 w-4" />
                                {t('Questions about this document?')}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </WebsiteLayout>
    );
}
