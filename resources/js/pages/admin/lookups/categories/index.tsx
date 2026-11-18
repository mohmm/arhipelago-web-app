import { Head } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';

import LookupIndex from '@/components/lookups/LookupIndex';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, PaginatedLookupItems } from '@/types';

const baseUrl = '/admin/lookups/categories';

export default function CategoriesIndex({ items }: { items: PaginatedLookupItems }) {
    const { t } = useLaravelReactI18n();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('Administration'), href: '/admin/lookups' },
        { title: t('Reference Tables'), href: '/admin/lookups' },
        { title: t('Categories'), href: baseUrl },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('Categories')} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <LookupIndex items={items} lookupLabel={t('Categories')} baseUrl={baseUrl} />
            </div>
        </AppLayout>
    );
}
