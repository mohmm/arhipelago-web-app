import { useLaravelReactI18n } from 'laravel-react-i18n';
import { cn } from '@/lib/utils';

/**
 * Whether something can be seen in the app: published, or still a draft.
 */
export default function StatusBadge({ published, publishedLabel, draftLabel }: { published: boolean; publishedLabel?: string; draftLabel?: string }) {
    const { t } = useLaravelReactI18n();

    return (
        <span
            className={cn(
                'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium',
                published ? 'bg-success-subtle text-success' : 'bg-muted text-muted-foreground',
            )}
        >
            <span className={cn('size-1.5 rounded-full', published ? 'bg-success' : 'bg-muted-foreground/60')} />
            {published ? (publishedLabel ?? t('Published')) : (draftLabel ?? t('Draft'))}
        </span>
    );
}
