import { useLaravelReactI18n } from 'laravel-react-i18n';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { cn } from '@/lib/utils';

export const CONTENT_LOCALES = ['ro', 'en', 'ar'] as const;

export type ContentLocale = (typeof CONTENT_LOCALES)[number];

type Props = {
    label: string;
    /** The attribute without its language, e.g. "name" for name_ro, name_en and name_ar. */
    name: string;
    values: Record<string, unknown>;
    onChange: (field: string, value: string) => void;
    errors: Partial<Record<string, string>>;
    multiline?: boolean;
    hint?: string;
};

/**
 * One piece of content in Romanian, English and Arabic. Romanian is required;
 * the dot on a language shows whether it is written yet, and the app falls
 * back to Romanian until it is.
 */
export default function TranslatedField({ label, name, values, onChange, errors, multiline = false, hint }: Props) {
    const { t } = useLaravelReactI18n();
    const firstWithError = CONTENT_LOCALES.find((locale) => errors[`${name}_${locale}`]);
    const [active, setActive] = useState<ContentLocale>(firstWithError ?? 'ro');
    const field = `${name}_${active}`;
    const value = (values[field] as string | null | undefined) ?? '';

    return (
        <div className="grid gap-2">
            <div className="flex items-center justify-between gap-3">
                <Label htmlFor={field}>
                    {label}
                    {active == 'ro' && <span className="text-primary"> *</span>}
                </Label>
                <div className="flex gap-1 rounded-lg bg-muted p-0.5" role="tablist" aria-label={t('Language')}>
                    {CONTENT_LOCALES.map((locale) => {
                        const filled = String(values[`${name}_${locale}`] ?? '').trim() != '';

                        return (
                            <button
                                key={locale}
                                type="button"
                                role="tab"
                                aria-selected={active == locale}
                                onClick={() => setActive(locale)}
                                className={cn(
                                    'flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-semibold uppercase',
                                    active == locale ? 'bg-background shadow-sm' : 'text-muted-foreground hover:text-foreground',
                                    errors[`${name}_${locale}`] && 'text-destructive',
                                )}
                            >
                                <span className={cn('size-1.5 rounded-full', filled ? 'bg-success' : 'bg-muted-foreground/40')} />
                                {locale}
                            </button>
                        );
                    })}
                </div>
            </div>

            {multiline ? (
                <Textarea
                    id={field}
                    dir={active == 'ar' ? 'rtl' : 'ltr'}
                    rows={5}
                    value={value}
                    onChange={(event) => onChange(field, event.target.value)}
                />
            ) : (
                <Input id={field} dir={active == 'ar' ? 'rtl' : 'ltr'} value={value} onChange={(event) => onChange(field, event.target.value)} />
            )}

            {hint && <p className="text-xs text-muted-foreground">{hint}</p>}
            <InputError message={errors[field]} />
        </div>
    );
}
