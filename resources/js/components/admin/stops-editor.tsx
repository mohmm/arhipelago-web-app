import { useLaravelReactI18n } from 'laravel-react-i18n';
import { ArrowDown, ArrowUp, Plus, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { SearchableSelect } from '@/components/ui/searchable-select';

export type PlaceOption = { value: number; label: string; latitude: number; longitude: number };

type Props = {
    stops: number[];
    places: PlaceOption[];
    onChange: (stops: number[]) => void;
};

/**
 * The stops of an experience, in the order the route visits them.
 */
export default function StopsEditor({ stops, places, onChange }: Props) {
    const { t } = useLaravelReactI18n();
    const options = places.map((place) => ({ value: String(place.value), label: place.label }));

    const move = (index: number, offset: number) => {
        const next = [...stops];
        [next[index], next[index + offset]] = [next[index + offset], next[index]];
        onChange(next);
    };

    const unused = places.find((place) => !stops.includes(place.value));

    return (
        <div className="grid gap-2">
            {stops.map((placeId, index) => (
                <div key={`${index}-${placeId}`} className="flex items-center gap-2">
                    <span className="flex size-7 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-semibold text-primary-foreground">{index + 1}</span>
                    <SearchableSelect
                        className="flex-1"
                        value={String(placeId)}
                        options={options.filter((option) => option.value == String(placeId) || !stops.includes(Number(option.value)))}
                        onChange={(value) => onChange(stops.map((current, position) => (position == index ? Number(value) : current)))}
                        placeholder={t('Choose a place')}
                        searchPlaceholder={t('Search places')}
                    />
                    <Button type="button" size="icon" variant="ghost" disabled={index == 0} onClick={() => move(index, -1)} aria-label={t('Move up')}>
                        <ArrowUp className="size-4" />
                    </Button>
                    <Button type="button" size="icon" variant="ghost" disabled={index == stops.length - 1} onClick={() => move(index, 1)} aria-label={t('Move down')}>
                        <ArrowDown className="size-4" />
                    </Button>
                    <Button type="button" size="icon" variant="ghost" onClick={() => onChange(stops.filter((_, position) => position != index))} aria-label={t('Remove stop')}>
                        <X className="size-4" />
                    </Button>
                </div>
            ))}
            <Button type="button" variant="outline" className="justify-self-start" disabled={unused == null} onClick={() => unused && onChange([...stops, unused.value])}>
                <Plus className="size-4" />
                {t('Add a stop')}
            </Button>
        </div>
    );
}
