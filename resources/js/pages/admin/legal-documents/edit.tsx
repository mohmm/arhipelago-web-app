import { Head, useForm } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { ExternalLink } from 'lucide-react';
import type { FormEventHandler } from 'react';

import { DateField } from '@/components/date-field';
import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { useLocalizedUrl } from '@/lib/locale';
import type { LegalDocumentSummary } from '@/pages/admin/legal-documents/index';
import { dashboard } from '@/routes';
import legalDocuments from '@/routes/admin/legal-documents';
import type { BreadcrumbItem } from '@/types';

type LegalDocumentDetail = LegalDocumentSummary & {
    body_ro: string;
    body_en: string;
    preview_ro: string;
    preview_en: string;
};

type Props = {
    document: LegalDocumentDetail;
};

export default function EditLegalDocument({ document }: Props) {
    const { t } = useLaravelReactI18n();
    const url = useLocalizedUrl();

    const { data, setData, put, processing, errors, isDirty } = useForm({
        title_ro: document.title_ro,
        title_en: document.title_en,
        body_ro: document.body_ro,
        body_en: document.body_en,
        version: document.version,
        effective_date: document.effective_date ?? '',
    });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('Dashboard'), href: dashboard().url },
        { title: t('Legal documents'), href: legalDocuments.index().url },
        { title: document.title, href: legalDocuments.edit(document.slug).url },
    ];

    const handleSubmit: FormEventHandler = (event) => {
        event.preventDefault();

        put(url(legalDocuments.update(document.slug).url), { preserveScroll: true });
    };

    /**
     * Romanian first, because it is the primary locale and the one a visitor
     * sees by default. Both languages are on the page rather than behind tabs
     * so a validation error can never land on a panel nobody is looking at.
     */
    const languages = [
        {
            key: 'ro' as const,
            label: t('Romanian'),
            title: data.title_ro,
            body: data.body_ro,
            titleError: errors.title_ro,
            bodyError: errors.body_ro,
            preview: document.preview_ro,
            onTitle: (value: string) => setData('title_ro', value),
            onBody: (value: string) => setData('body_ro', value),
        },
        {
            key: 'en' as const,
            label: t('English'),
            title: data.title_en,
            body: data.body_en,
            titleError: errors.title_en,
            bodyError: errors.body_en,
            preview: document.preview_en,
            onTitle: (value: string) => setData('title_en', value),
            onBody: (value: string) => setData('body_en', value),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${document.title} - ${t('Legal documents')}`} />

            <form onSubmit={handleSubmit} className="flex h-full flex-1 flex-col gap-6 p-4">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <HeadingSmall
                        title={document.title}
                        description={t('Saving publishes the new wording straight away. The text it replaces is kept in the activity log.')}
                    />
                    <div className="flex items-center gap-2">
                        {document.public_url && (
                            <Button asChild variant="outline">
                                <a href={document.public_url} target="_blank" rel="noopener noreferrer">
                                    <ExternalLink className="h-4 w-4" />
                                    {t('View the public page')}
                                </a>
                            </Button>
                        )}
                        <Button type="submit" disabled={processing || !isDirty}>
                            {processing ? t('Saving...') : t('Save')}
                        </Button>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('Publication')}</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="version">{t('Version')}</Label>
                                <Input id="version" value={data.version} onChange={(e) => setData('version', e.target.value)} />
                                <p className="text-xs text-muted-foreground">
                                    {t('Shown to customers. Change it whenever the meaning changes, not for a typo.')}
                                </p>
                                <InputError message={errors.version} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="effective_date">{t('In force from')}</Label>
                                <DateField id="effective_date" value={data.effective_date} onChange={(value) => setData('effective_date', value)} />
                                <p className="text-xs text-muted-foreground">
                                    {t('The date this wording applies from. Leave empty while it is still a draft.')}
                                </p>
                                <InputError message={errors.effective_date} />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {languages.map((language) => (
                    <Card key={language.key}>
                        <CardHeader>
                            <CardTitle>{language.label}</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-2">
                                <Label htmlFor={`title_${language.key}`}>{t('Title')}</Label>
                                <Input id={`title_${language.key}`} value={language.title} onChange={(e) => language.onTitle(e.target.value)} />
                                <InputError message={language.titleError} />
                            </div>

                            <div className="grid gap-4 xl:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor={`body_${language.key}`}>{t('Content')}</Label>
                                    <Textarea
                                        id={`body_${language.key}`}
                                        value={language.body}
                                        onChange={(e) => language.onBody(e.target.value)}
                                        className="min-h-[28rem] font-mono text-xs leading-relaxed"
                                        spellCheck={false}
                                    />
                                    <p className="text-xs text-muted-foreground">
                                        {t('Markdown. Start headings at ## — the title above is the page heading. HTML is removed.')}{' '}
                                        {t('[APP_URL], [APP_HOST] and [SESSION_COOKIE] show this site’s address, domain and session cookie.')}
                                    </p>
                                    <InputError message={language.bodyError} />
                                </div>

                                <div className="grid gap-2">
                                    <Label>{t('Published version')}</Label>
                                    <div className="max-h-[28rem] overflow-y-auto rounded-md border border-border p-4">
                                        <div
                                            className="prose prose-sm max-w-none prose-neutral dark:prose-invert prose-table:block prose-table:overflow-x-auto"
                                            dangerouslySetInnerHTML={{ __html: language.preview }}
                                        />
                                    </div>
                                    <p className="text-xs text-muted-foreground">{t('What visitors see right now. It updates when you save.')}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                ))}
            </form>
        </AppLayout>
    );
}
