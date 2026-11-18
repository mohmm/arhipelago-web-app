import { Link } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { ArrowLeft, Edit } from 'lucide-react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import type { LookupItem } from '@/types';

interface Props {
    item: LookupItem;
    baseUrl: string;
}

export default function LookupShow({ item, baseUrl }: Props) {
    const { t } = useLaravelReactI18n();

    function formatDate(dateString: string) {
        return new Date(dateString).toLocaleDateString(undefined, {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    }

    return (
        <div className="space-y-6">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <Button variant="outline" asChild>
                    <Link href={baseUrl}>
                        <ArrowLeft className="h-4 w-4" />
                        {t('Back to list')}
                    </Link>
                </Button>
                <Button asChild>
                    <Link href={`${baseUrl}/${item.id}/edit`}>
                        <Edit className="h-4 w-4" />
                        {t('Edit')}
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>{t('Basic information')}</CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                    <div className="grid gap-4 sm:grid-cols-2">
                        <div>
                            <p className="text-sm font-medium text-muted-foreground">
                                {t('Name (English)')}
                            </p>
                            <p className="mt-1 text-sm">{item.name_en}</p>
                        </div>
                        <div>
                            <p className="text-sm font-medium text-muted-foreground">
                                {t('Name (Romanian)')}
                            </p>
                            <p className="mt-1 text-sm">{item.name_ro}</p>
                        </div>
                    </div>
                    <Separator />
                    <div className="grid gap-4 sm:grid-cols-3">
                        <div>
                            <p className="text-sm font-medium text-muted-foreground">{t('Slug')}</p>
                            <code className="mt-1 inline-block rounded bg-muted px-1.5 py-0.5 text-xs">
                                {item.slug}
                            </code>
                        </div>
                        <div>
                            <p className="text-sm font-medium text-muted-foreground">
                                {t('Sort order')}
                            </p>
                            <p className="mt-1 text-sm">{item.sort_order}</p>
                        </div>
                        <div>
                            <p className="text-sm font-medium text-muted-foreground">{t('Status')}</p>
                            <Badge variant={item.is_active ? 'default' : 'secondary'} className="mt-1">
                                {item.is_active ? t('Active') : t('Inactive')}
                            </Badge>
                        </div>
                    </div>
                </CardContent>
            </Card>

            {(item.color || item.icon || item.image_url) && (
                <Card>
                    <CardHeader>
                        <CardTitle>{t('Appearance')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 sm:grid-cols-2">
                            {item.color && (
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">
                                        {t('Color')}
                                    </p>
                                    <div className="mt-1 flex items-center gap-2">
                                        <div
                                            className="h-6 w-6 rounded-md border"
                                            style={{ backgroundColor: item.color }}
                                        />
                                        <span className="text-sm">{item.color}</span>
                                    </div>
                                </div>
                            )}
                            {item.icon && (
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">
                                        {t('Icon')}
                                    </p>
                                    <p className="mt-1 text-sm">{item.icon}</p>
                                </div>
                            )}
                            {item.image_url && (
                                <div className="sm:col-span-2">
                                    <p className="text-sm font-medium text-muted-foreground">
                                        {t('Image URL')}
                                    </p>
                                    {/^https?:\/\//i.test(item.image_url) ? (
                                        <a
                                            href={item.image_url}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="mt-1 block truncate text-sm text-primary hover:underline"
                                        >
                                            {item.image_url}
                                        </a>
                                    ) : (
                                        <p className="mt-1 block truncate text-sm">{item.image_url}</p>
                                    )}
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>
            )}

            <Card>
                <CardContent>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <div>
                            <p className="text-sm font-medium text-muted-foreground">{t('Created')}</p>
                            <p className="mt-1 text-sm">{formatDate(item.created_at)}</p>
                        </div>
                        <div>
                            <p className="text-sm font-medium text-muted-foreground">{t('Updated')}</p>
                            <p className="mt-1 text-sm">{formatDate(item.updated_at)}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}
