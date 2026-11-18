import { Head, Link } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { ArrowLeft } from 'lucide-react';

import LookupForm from '@/components/lookups/LookupForm';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

const baseUrl = '/admin/lookups/categories';

export default function CategoriesCreate() {
    const { t } = useLaravelReactI18n();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('Administration'), href: '/admin/lookups' },
        { title: t('Reference Tables'), href: '/admin/lookups' },
        { title: t('Categories'), href: baseUrl },
        { title: t('New'), href: `${baseUrl}/create` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${t('New')} - ${t('Categories')}`} />

            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <div className="flex items-center gap-4">
                    <Button variant="outline" size="icon" asChild>
                        <Link href={baseUrl}>
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h2 className="text-xl font-semibold tracking-tight">{t('New')}</h2>
                        <p className="text-sm text-muted-foreground">{t('Categories')}</p>
                    </div>
                </div>

                <LookupForm baseUrl={baseUrl} />
            </div>
        </AppLayout>
    );
}
