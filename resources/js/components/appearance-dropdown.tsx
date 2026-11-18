import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Check, Monitor, Moon, Sun } from 'lucide-react';
import type { HTMLAttributes } from 'react';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useAppearance } from '@/hooks/use-appearance';
import type { Appearance } from '@/hooks/use-appearance';

const options: { value: Appearance; icon: typeof Sun; label: string }[] = [
    { value: 'light', icon: Sun, label: 'Light' },
    { value: 'dark', icon: Moon, label: 'Dark' },
    { value: 'system', icon: Monitor, label: 'System' },
];

/**
 * The website's theme switch: light, dark or the device's own setting.
 */
export default function AppearanceToggleDropdown({ className = '', ...props }: HTMLAttributes<HTMLDivElement>) {
    const { appearance, updateAppearance } = useAppearance();
    const { t } = useLaravelReactI18n();

    const current = options.find((option) => option.value === appearance) ?? options[2];
    const CurrentIcon = current.icon;

    return (
        <div className={className} {...props}>
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button variant="ghost" size="icon" className="size-9">
                        <CurrentIcon className="size-4" />
                        <span className="sr-only">{t('Change theme')}</span>
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" className="w-40">
                    {options.map(({ value, icon: Icon, label }) => (
                        <DropdownMenuItem key={value} onClick={() => updateAppearance(value)} className="gap-2">
                            <Icon className="size-4" />
                            <span className="flex-1">{t(label)}</span>
                            {/* The trigger cannot show it: the system icon looks the same whichever way it resolved. */}
                            {appearance === value && <Check className="size-4 opacity-60" />}
                        </DropdownMenuItem>
                    ))}
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    );
}
