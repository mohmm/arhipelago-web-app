import { Head, Link } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { ArrowLeft, Edit, Shield } from 'lucide-react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

const baseUrl = '/admin/users';

type UserItem = {
    id: number;
    first_name: string;
    last_name: string;
    full_name: string;
    email: string;
    role: string | null;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
};

export default function UsersShow({ item }: { item: UserItem }) {
    const { t } = useLaravelReactI18n();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('Administration'), href: '/admin/lookups' },
        { title: t('Users'), href: baseUrl },
        { title: item.full_name, href: `${baseUrl}/${item.id}` },
    ];

    function formatDate(dateString: string) {
        return new Date(dateString).toLocaleDateString(undefined, {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    }

    function formatRole(role: string | null) {
        if (!role) {
            return '-';
        }

        return role.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${item.full_name} - ${t('Users')}`} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
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
                                    <p className="text-sm font-medium text-muted-foreground">{t('First name')}</p>
                                    <p className="mt-1 text-sm">{item.first_name}</p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">{t('Last name')}</p>
                                    <p className="mt-1 text-sm">{item.last_name}</p>
                                </div>
                            </div>
                            <Separator />
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">{t('Email')}</p>
                                    <p className="mt-1 text-sm">{item.email}</p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">{t('Role')}</p>
                                    <Badge variant="secondary" className="mt-1">
                                        <Shield className="mr-1 h-3 w-3" />
                                        {formatRole(item.role)}
                                    </Badge>
                                </div>
                            </div>
                            <Separator />
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">{t('Verified')}</p>
                                <Badge
                                    variant={item.email_verified_at ? 'default' : 'secondary'}
                                    className="mt-1"
                                >
                                    {item.email_verified_at ? t('Verified') : t('Unverified')}
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>

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
            </div>
        </AppLayout>
    );
}
