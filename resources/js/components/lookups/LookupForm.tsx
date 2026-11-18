import { useForm } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import type { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { useLocalizedUrl } from '@/lib/locale';
import type { LookupItem } from '@/types';

interface Props {
    item?: LookupItem;
    baseUrl: string;
}

export default function LookupForm({ item, baseUrl }: Props) {
    const { t } = useLaravelReactI18n();
    const url = useLocalizedUrl();
    const isEditing = !!item;

    const { data, setData, post, put, processing, errors } = useForm({
        name_en: item?.name_en ?? '',
        name_ro: item?.name_ro ?? '',
        name_ar: item?.name_ar ?? '',
        slug: item?.slug ?? '',
        sort_order: item?.sort_order ?? 0,
        color: item?.color ?? '',
        icon: item?.icon ?? '',
        image_url: item?.image_url ?? '',
        is_active: item?.is_active ?? true,
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();

        const options = {
            preserveScroll: true,
        };

        if (isEditing) {
            put(url(`${baseUrl}/${item.id}`), options);
        } else {
            post(url(baseUrl), options);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>{t('Basic information')}</CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                    <div className="grid gap-4 sm:grid-cols-3">
                        <div className="grid gap-2">
                            <Label htmlFor="name_en">{t('Name (English)')}</Label>
                            <Input
                                id="name_en"
                                name="name_en"
                                value={data.name_en}
                                onChange={(e) => setData('name_en', e.target.value)}
                            />
                            <InputError message={errors.name_en} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="name_ro">{t('Name (Romanian)')}</Label>
                            <Input
                                id="name_ro"
                                name="name_ro"
                                value={data.name_ro}
                                onChange={(e) => setData('name_ro', e.target.value)}
                            />
                            <InputError message={errors.name_ro} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="name_ar">{t('Name (Arabic)')}</Label>
                            <Input
                                id="name_ar"
                                name="name_ar"
                                dir="rtl"
                                value={data.name_ar}
                                onChange={(e) => setData('name_ar', e.target.value)}
                            />
                            <InputError message={errors.name_ar} />
                        </div>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="slug">{t('Slug')}</Label>
                            <Input
                                id="slug"
                                name="slug"
                                value={data.slug}
                                onChange={(e) => setData('slug', e.target.value)}
                                placeholder="museums"
                            />
                            <p className="text-xs text-muted-foreground">
                                {t('URL-friendly identifier. Leave blank to generate from the English name.')}
                            </p>
                            <InputError message={errors.slug} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="sort_order">{t('Sort order')}</Label>
                            <Input
                                id="sort_order"
                                name="sort_order"
                                type="number"
                                value={data.sort_order}
                                onChange={(e) => setData('sort_order', parseInt(e.target.value) || 0)}
                            />
                            <InputError message={errors.sort_order} />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>{t('Appearance')}</CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="color">
                                {t('Color')}{' '}
                                <span className="text-muted-foreground">({t('optional')})</span>
                            </Label>
                            <div className="flex items-center gap-2">
                                <Input
                                    id="color"
                                    name="color"
                                    type="color"
                                    value={data.color || '#3B82F6'}
                                    onChange={(e) => setData('color', e.target.value)}
                                    className="h-9 w-14 cursor-pointer p-1"
                                />
                                <Input
                                    value={data.color}
                                    onChange={(e) => setData('color', e.target.value)}
                                    placeholder="#3B82F6"
                                    className="flex-1"
                                />
                            </div>
                            <InputError message={errors.color} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="icon">
                                {t('Icon')}{' '}
                                <span className="text-muted-foreground">({t('optional')})</span>
                            </Label>
                            <Input
                                id="icon"
                                name="icon"
                                value={data.icon}
                                onChange={(e) => setData('icon', e.target.value)}
                                placeholder="map-pin"
                            />
                            <p className="text-xs text-muted-foreground">
                                {t('Lucide icon name, e.g. map-pin.')}
                            </p>
                            <InputError message={errors.icon} />
                        </div>
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="image_url">
                            {t('Image URL')}{' '}
                            <span className="text-muted-foreground">({t('optional')})</span>
                        </Label>
                        <Input
                            id="image_url"
                            name="image_url"
                            value={data.image_url}
                            onChange={(e) => setData('image_url', e.target.value)}
                            placeholder="https://..."
                        />
                        <InputError message={errors.image_url} />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent className="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex items-center gap-3">
                        <Switch
                            id="is_active"
                            checked={data.is_active}
                            onCheckedChange={(checked) => setData('is_active', checked)}
                        />
                        <div>
                            <Label htmlFor="is_active">{t('Active')}</Label>
                            <p className="text-xs text-muted-foreground">
                                {t('Only active items are shown to visitors.')}
                            </p>
                        </div>
                    </div>
                    <Button type="submit" disabled={processing}>
                        {processing ? t('Saving...') : isEditing ? t('Update') : t('Create')}
                    </Button>
                </CardContent>
            </Card>
        </form>
    );
}
