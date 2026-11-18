import { Head } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';

import LookupShow from '@/components/lookups/LookupShow';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, LookupItem } from '@/types';

const baseUrl = '/admin/lookups/categories';

export default function CategoriesShow({ item }: { item: LookupItem }) {
    const { t } = useLaravelReactI18n();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('Administration'), href: '/admin/lookups' },
        { title: t('Reference Tables'), href: '/admin/lookups' },
        { title: t('Categories'), href: baseUrl },
        { title: item.translated_name, href: `${baseUrl}/${item.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${item.translated_name} - ${t('Categories')}`} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <LookupShow item={item} baseUrl={baseUrl} />
            </div>
        </AppLayout>
    );
}
