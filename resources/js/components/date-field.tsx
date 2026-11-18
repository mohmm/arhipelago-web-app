import { useLaravelReactI18n } from 'laravel-react-i18n';

import { DatePicker } from '@/components/ui/date-picker';

type DateFieldProps = {
    value?: string;
    onChange: (value: string) => void;
    minValue?: string;
    maxValue?: string;
    disabledDates?: string[];
    placeholder?: string;
    className?: string;
    id?: string;
    disabled?: boolean;
    'aria-label'?: string;
};

/**
 * The date control the whole application uses.
 *
 * `DatePicker` under `ui/` is deliberately free of application concerns and
 * takes every label as a prop, which is right for a primitive and tedious for
 * the eight screens that need one. This supplies the labels and the reader's
 * language once, so no screen can ship a calendar that says "Reset" and
 * "January" to a Romanian owner — the mailbox forms did exactly that until this
 * existed, because the defaults were English and nobody passed anything.
 */
export function DateField({ ...props }: DateFieldProps) {
    const { t, currentLocale } = useLaravelReactI18n();

    return (
        <DatePicker
            {...props}
            locale={currentLocale() ?? 'ro'}
            placeholder={props.placeholder ?? t('Choose a date')}
            resetLabel={t('Clear')}
            previousLabel={t('Previous month')}
            nextLabel={t('Next month')}
            unavailableLabel={t('Not available')}
        />
    );
}
