import { Head, Link } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { ExternalLink, Pencil } from 'lucide-react';

import HeadingSmall from '@/components/heading-small';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import legalDocuments from '@/routes/admin/legal-documents';
import type { BreadcrumbItem } from '@/types';

export type LegalDocumentSummary = {
    slug: string;
    title_ro: string;
    title_en: string;
    title: string;
    version: string;
    effective_date: string | null;
    updated_at: string | null;
    updated_by: string | null;
    public_url: string | null;
};

type Props = {
    documents: LegalDocumentSummary[];
};

export default function LegalDocumentsIndex({ documents }: Props) {
    const { t, currentLocale } = useLaravelReactI18n();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('Dashboard'), href: dashboard().url },
        { title: t('Legal documents'), href: legalDocuments.index().url },
    ];

    function formatDate(value: string | null) {
        if (!value) {
            return '—';
        }

        return new Date(value).toLocaleDateString(currentLocale() ?? 'ro', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('Legal documents')} />

            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <HeadingSmall
                    title={t('Legal documents')}
                    description={t('The published terms, notices and agreements. Editing one changes the live page immediately.')}
                />

                <Card>
                    <CardContent className="p-0">
                        <div className="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('Document')}</TableHead>
                                        <TableHead>{t('Version')}</TableHead>
                                        <TableHead>{t('In force from')}</TableHead>
                                        <TableHead>{t('Last edited')}</TableHead>
                                        <TableHead className="text-right">{t('Actions')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {documents.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={5} className="py-10 text-center text-sm text-muted-foreground">
                                                {t('No legal documents yet. Run the seeder to install them.')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        documents.map((item) => (
                                            <TableRow key={item.slug}>
                                                <TableCell>
                                                    <span className="font-medium">{item.title}</span>
                                                    <span className="block text-xs text-muted-foreground">{item.slug}</span>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge variant="secondary">{item.version}</Badge>
                                                </TableCell>
                                                <TableCell className="tabular-nums">{formatDate(item.effective_date)}</TableCell>
                                                <TableCell className="tabular-nums">
                                                    {formatDate(item.updated_at)}
                                                    {item.updated_by && (
                                                        <span className="block text-xs text-muted-foreground">{item.updated_by}</span>
                                                    )}
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex items-center justify-end gap-1">
                                                        {item.public_url && (
                                                            <Button asChild variant="ghost" size="icon" className="h-7 w-7">
                                                                <a
                                                                    href={item.public_url}
                                                                    target="_blank"
                                                                    rel="noopener noreferrer"
                                                                    aria-label={t('View the public page')}
                                                                >
                                                                    <ExternalLink className="h-4 w-4" />
                                                                </a>
                                                            </Button>
                                                        )}
                                                        <Button asChild variant="ghost" size="icon" className="h-7 w-7">
                                                            <Link href={legalDocuments.edit(item.slug)} aria-label={t('Edit')}>
                                                                <Pencil className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
