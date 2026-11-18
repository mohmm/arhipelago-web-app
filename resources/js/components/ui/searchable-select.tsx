import { Check, ChevronsUpDown, Search } from 'lucide-react';
import { useEffect, useId, useMemo, useRef, useState } from 'react';

import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';

export type SearchableOption = {
    value: string;
    label: string;
    /** A second line — a county under a commune, a code under a name. */
    hint?: string;
};

type SearchableSelectProps = {
    value: string;
    onChange: (value: string) => void;
    options: SearchableOption[];
    placeholder?: string;
    searchPlaceholder?: string;
    emptyLabel?: string;
    moreLabel?: string;
    /** Below this many options the search box is pointless and is not drawn. */
    searchThreshold?: number;
    className?: string;
    contentClassName?: string;
    'aria-label'?: string;
    id?: string;
    disabled?: boolean;
};

/** The most rows drawn at once. A commune list is 3.238 entries long. */
const MAX_VISIBLE = 100;

/**
 * Diacritic- and case-insensitive, which for Romanian is the whole point.
 *
 * Somebody looking for Brașov types "brasov", and somebody looking for Bistrița
 * types "bistrita". A filter that only matched the typed form would make the
 * search box worse than scrolling.
 *
 * Both encodings of ș and ț fold the same way after NFD — the comma-below form
 * the standard prescribes and the cedilla form half of Romanian Windows still
 * produces — so a list typed either way is searchable either way.
 */
function fold(value: string): string {
    return value
        .normalize('NFD')
        .replace(/\p{Diacritic}/gu, '')
        .toLocaleLowerCase();
}

/**
 * A select you can type into.
 *
 * The native `<select>` and the plain Radix one are both fine at ten options
 * and unusable at forty: the county list is a scroll with no way to jump, and
 * on a phone it is a full-height wall of names. This is the same control with a
 * filter at the top — one component, so every long list in the application
 * behaves identically rather than each screen inventing its own.
 *
 * Rendered in a popover rather than as a native dropdown on purpose. A native
 * one is drawn by the operating system, which is why the same page looks like
 * three different products across Safari, Chrome and Android; this looks the
 * same everywhere and can carry a second line of context per row.
 */
export function SearchableSelect({
    value,
    onChange,
    options,
    placeholder = '',
    searchPlaceholder = '',
    emptyLabel = '',
    moreLabel = '',
    searchThreshold = 8,
    className,
    contentClassName,
    'aria-label': ariaLabel,
    id,
    disabled = false,
}: SearchableSelectProps) {
    const [open, setOpen] = useState(false);
    const [query, setQuery] = useState('');
    const [active, setActive] = useState(0);

    const listId = useId();
    const listRef = useRef<HTMLDivElement>(null);
    const triggerRef = useRef<HTMLButtonElement>(null);

    const selected = options.find((option) => option.value == value) ?? null;
    const showsSearch = options.length >= searchThreshold;

    const matches = useMemo(() => {
        const needle = fold(query.trim());

        if (needle == '') {
            return options;
        }

        return options.filter((option) => fold(option.label).includes(needle) || fold(option.hint ?? '').includes(needle));
    }, [options, query]);

    const visible = matches.slice(0, MAX_VISIBLE);
    const hidden = matches.length - visible.length;

    // Opening lands on the current choice rather than at the top, so the list
    // starts where the reader already is.
    useEffect(() => {
        if (!open) {
            return;
        }

        setQuery('');
        setActive(Math.max(0, options.findIndex((option) => option.value == value)));
    }, [open, options, value]);

    // Typing invalidates the highlight, which must never point past the list.
    useEffect(() => {
        setActive((current) => (current >= visible.length ? 0 : current));
    }, [visible.length]);

    useEffect(() => {
        if (!open) {
            return;
        }

        listRef.current?.querySelector('[data-active="true"]')?.scrollIntoView({ block: 'nearest' });
    }, [active, open]);

    function choose(next: string): void {
        onChange(next);
        setOpen(false);
        // Focus goes back where it came from, so a keyboard user is not
        // dropped at the top of the document.
        triggerRef.current?.focus();
    }

    function onKeyDown(event: React.KeyboardEvent): void {
        if (event.key == 'ArrowDown' || event.key == 'ArrowUp') {
            event.preventDefault();
            setActive((current) => {
                const next = current + (event.key == 'ArrowDown' ? 1 : -1);

                return (next + visible.length) % Math.max(1, visible.length);
            });

            return;
        }

        if (event.key == 'Home' || event.key == 'End') {
            event.preventDefault();
            setActive(event.key == 'Home' ? 0 : Math.max(0, visible.length - 1));

            return;
        }

        if (event.key == 'Enter') {
            event.preventDefault();
            const option = visible[active];

            if (option) {
                choose(option.value);
            }
        }
    }

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <Button
                    ref={triggerRef}
                    id={id}
                    type="button"
                    variant="outline"
                    role="combobox"
                    aria-expanded={open}
                    aria-label={ariaLabel}
                    aria-controls={open ? listId : undefined}
                    disabled={disabled}
                    className={cn('h-9 w-full justify-between px-3 font-normal', !selected && 'text-muted-foreground', className)}
                >
                    <span className="truncate text-left">{selected?.label ?? placeholder}</span>
                    <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" aria-hidden="true" />
                </Button>
            </PopoverTrigger>

            {/*
                Width is taken from the trigger so the panel lines up with the
                control instead of floating at some inherited default, and the
                height is capped in viewport units so a long list never runs off
                the bottom of a phone.
            */}
            <PopoverContent
                align="start"
                onKeyDown={onKeyDown}
                className={cn('w-(--radix-popover-trigger-width) min-w-56 p-0', contentClassName)}
            >
                {showsSearch && (
                    <div className="flex items-center gap-2 border-b px-3">
                        <Search className="h-4 w-4 shrink-0 opacity-50" aria-hidden="true" />
                        <input
                            // eslint-disable-next-line jsx-a11y/no-autofocus -- the popover exists to be typed into
                            autoFocus
                            type="text"
                            role="searchbox"
                            value={query}
                            onChange={(event) => setQuery(event.target.value)}
                            placeholder={searchPlaceholder}
                            aria-label={searchPlaceholder}
                            className="h-10 w-full bg-transparent text-sm outline-hidden placeholder:text-muted-foreground"
                        />
                    </div>
                )}

                <div ref={listRef} id={listId} role="listbox" className="max-h-[min(20rem,50vh)] overflow-y-auto overscroll-contain p-1">
                    {visible.length == 0 && <p className="px-3 py-6 text-center text-sm text-muted-foreground">{emptyLabel}</p>}

                    {visible.map((option, index) => {
                        const isSelected = option.value == value;

                        return (
                            <button
                                key={option.value}
                                type="button"
                                role="option"
                                aria-selected={isSelected}
                                data-active={index == active}
                                onClick={() => choose(option.value)}
                                onMouseMove={() => setActive(index)}
                                className={cn(
                                    'flex w-full items-center gap-2 rounded-sm px-2 py-2 text-left text-sm outline-hidden',
                                    'data-[active=true]:bg-accent data-[active=true]:text-accent-foreground',
                                )}
                            >
                                <Check className={cn('h-4 w-4 shrink-0', isSelected ? 'opacity-100' : 'opacity-0')} aria-hidden="true" />
                                <span className="min-w-0 flex-1">
                                    <span className="block truncate">{option.label}</span>
                                    {option.hint && <span className="block truncate text-xs text-muted-foreground">{option.hint}</span>}
                                </span>
                            </button>
                        );
                    })}

                    {hidden > 0 && <p className="px-3 py-2 text-center text-xs text-muted-foreground">{moreLabel}</p>}
                </div>
            </PopoverContent>
        </Popover>
    );
}
