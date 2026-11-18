import { router, useForm } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { ImagePlus, Star, Trash2 } from 'lucide-react';
import type { FormEvent } from 'react';
import ConfirmButton from '@/components/admin/confirm-button';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useLocalizedUrl } from '@/lib/locale';

export type Photo = {
    id: number;
    thumb_url: string;
    url: string;
    author: string | null;
    license: string | null;
    source_url: string | null;
};

export type Option = { value: string; label: string };

/**
 * The gallery of a point of interest. The first photo is the cover the app
 * shows everywhere. The app shows photos without a credit, so only photos
 * whose licence asks for none can be added: the team's own, public domain or CC0.
 */
export default function PhotoManager({ placeId, photos, licenses }: { placeId: number; photos: Photo[]; licenses: Option[] }) {
    const { t } = useLaravelReactI18n();
    const url = useLocalizedUrl();
    const base = url(`/admin/points-of-interest/${placeId}/photos`);
    const form = useForm<{ photo: File | null; license: string; author: string; source_url: string }>({
        photo: null,
        license: '',
        author: '',
        source_url: '',
    });

    const upload = (event: FormEvent) => {
        event.preventDefault();
        form.post(base, { forceFormData: true, preserveScroll: true, onSuccess: () => form.reset() });
    };

    return (
        <div className="grid gap-6">
            {photos.length == 0 ? (
                <p className="rounded-xl border border-dashed p-6 text-center text-sm text-muted-foreground">{t('No photos yet. A place needs at least one photo before it can be published.')}</p>
            ) : (
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {photos.map((photo, index) => (
                        <figure key={photo.id} className="overflow-hidden rounded-xl border bg-card">
                            <div className="relative">
                                <img src={photo.thumb_url} alt="" className="aspect-[4/3] w-full object-cover" />
                                {index == 0 && (
                                    <span className="absolute top-2 left-2 rounded-full bg-primary px-2.5 py-0.5 text-xs font-medium text-primary-foreground">{t('Cover')}</span>
                                )}
                            </div>
                            <figcaption className="grid gap-3 p-3">
                                <p className="text-xs text-muted-foreground">{[photo.license, photo.author].filter(Boolean).join(' · ')}</p>
                                <div className="flex gap-2">
                                    {index > 0 && (
                                        <Button type="button" size="sm" variant="outline" onClick={() => router.post(url(`/admin/points-of-interest/${placeId}/photos/${photo.id}/cover`), {}, { preserveScroll: true })}>
                                            <Star className="size-4" />
                                            {t('Make cover')}
                                        </Button>
                                    )}
                                    <ConfirmButton
                                        title={t('Delete this photo?')}
                                        description={t('The photo is removed from the app straight away.')}
                                        confirmLabel={t('Delete')}
                                        onConfirm={() => router.delete(url(`/admin/points-of-interest/${placeId}/photos/${photo.id}`), { preserveScroll: true })}
                                    >
                                        <Button type="button" size="sm" variant="ghost" className="text-destructive hover:text-destructive">
                                            <Trash2 className="size-4" />
                                            {t('Delete')}
                                        </Button>
                                    </ConfirmButton>
                                </div>
                            </figcaption>
                        </figure>
                    ))}
                </div>
            )}

            <form onSubmit={upload} className="grid gap-4 rounded-xl border bg-muted/30 p-4">
                <div className="grid gap-2">
                    <Label htmlFor="photo">{t('Add a photo')}</Label>
                    <Input id="photo" type="file" accept="image/jpeg,image/png,image/webp" onChange={(event) => form.setData('photo', event.target.files?.[0] ?? null)} />
                    <InputError message={form.errors.photo} />
                </div>
                <div className="grid gap-4 sm:grid-cols-3">
                    <div className="grid gap-2">
                        <Label htmlFor="license">{t('Licence')}</Label>
                        <Select value={form.data.license} onValueChange={(value) => form.setData('license', value)}>
                            <SelectTrigger id="license">
                                <SelectValue placeholder={t('Choose the licence')} />
                            </SelectTrigger>
                            <SelectContent>
                                {licenses.map((license) => (
                                    <SelectItem key={license.value} value={license.value}>
                                        {license.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError message={form.errors.license} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="author">{t('Author (optional)')}</Label>
                        <Input id="author" value={form.data.author} onChange={(event) => form.setData('author', event.target.value)} placeholder={t('Who took the photo')} />
                        <InputError message={form.errors.author} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="source_url">{t('Source (optional)')}</Label>
                        <Input id="source_url" value={form.data.source_url} onChange={(event) => form.setData('source_url', event.target.value)} placeholder="https://commons.wikimedia.org/…" />
                        <InputError message={form.errors.source_url} />
                    </div>
                </div>
                <p className="text-xs text-muted-foreground">{t('The app shows photos without a credit, so only your own photos, public domain or CC0 photos can be added.')}</p>
                <Button type="submit" disabled={form.processing || form.data.photo == null} className="justify-self-start">
                    <ImagePlus className="size-4" />
                    {t('Upload photo')}
                </Button>
            </form>
        </div>
    );
}
