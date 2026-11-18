import { Head, Link } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { ArrowRight, Flag, Hash, Tags } from 'lucide-react';

import Heading from '@/components/heading';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, LookupType } from '@/types';

const iconMap: Record<string, React.ElementType> = {
    tags: Tags,
    hash: Hash,
    flag: Flag,
};

export default function LookupsIndex({ lookupTypes }: { lookupTypes: LookupType[] }) {
    const { t } = useLaravelReactI18n();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('Administration'), href: '/admin/lookups' },
        { title: t('Reference Tables'), href: '/admin/lookups' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('Reference Tables')} />

            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <Heading
                    title={t('Reference Tables')}
                    description={t('Manage categories, topics and other reference data.')}
                />

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {lookupTypes.map((type) => {
                        const Icon = iconMap[type.icon] ?? Tags;

                        return (
                            <Link key={type.slug} href={type.href} className="group">
                                <Card className="h-full transition-shadow hover:shadow-md">
                                    <CardHeader>
                                        <div className="flex items-center justify-between">
                                            <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                                <Icon className="h-5 w-5" />
                                            </div>
                                            <ArrowRight className="h-4 w-4 text-muted-foreground transition-transform group-hover:translate-x-1" />
                                        </div>
                                        <CardTitle className="mt-2">{type.name}</CardTitle>
                                        <CardDescription>{type.description}</CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <p className="text-sm text-muted-foreground">
                                            <span className="font-semibold text-foreground">
                                                {type.count}
                                            </span>{' '}
                                            {type.count === 1 ? t('item') : t('items')}
                                        </p>
                                    </CardContent>
                                </Card>
                            </Link>
                        );
                    })}
                </div>
            </div>
        </AppLayout>
    );
}
