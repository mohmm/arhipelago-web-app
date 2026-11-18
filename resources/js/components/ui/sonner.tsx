import { useAppearance } from '@/hooks/use-appearance';
import {
    CircleCheckIcon,
    InfoIcon,
    Loader2Icon,
    OctagonXIcon,
    TriangleAlertIcon,
} from 'lucide-react';
import { Toaster as Sonner, type ToasterProps } from 'sonner';

/**
 * Where every toast appears: centred at the top, the same for left-to-right
 * and right-to-left pages. Shown through `@/lib/toast`, never directly.
 */
function Toaster({ ...props }: ToasterProps) {
    const { appearance } = useAppearance();
    const dir = document.documentElement.getAttribute('dir') ?? 'ltr';

    return (
        <Sonner
            theme={appearance as ToasterProps['theme']}
            className="toaster group"
            position="top-center"
            richColors
            closeButton
            visibleToasts={3}
            dir={dir as 'ltr' | 'rtl'}
            icons={{
                success: <CircleCheckIcon className="size-4" />,
                info: <InfoIcon className="size-4" />,
                warning: <TriangleAlertIcon className="size-4" />,
                error: <OctagonXIcon className="size-4" />,
                loading: <Loader2Icon className="size-4 animate-spin" />,
            }}
            style={
                {
                    '--normal-bg': 'var(--popover)',
                    '--normal-text': 'var(--popover-foreground)',
                    '--normal-border': 'var(--border)',
                    '--border-radius': 'var(--radius)',
                } as React.CSSProperties
            }
            {...props}
        />
    );
}

export { Toaster };
