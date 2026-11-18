import { CalendarIcon, ChevronLeft, ChevronRight } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';

import { Button } from '@/components/ui/button';
import { addDays, formatDateValue, formatDisplayDate, formatMonthName, monthGrid, parseDateValue, startOfMonth, weekdayNames } from '@/components/ui/date-picker';
import { Label } from '@/components/ui/label';
import { Popover, PopoverAnchor, PopoverContent } from '@/components/ui/popover';
import { cn } from '@/lib/utils';

/** What one day is, in the calendar as it currently stands. */
type DayState = 'out' | 'taken' | 'departure' | 'free';

type Labels = {
    /** Field labels — "Arrival" and "Departure". */
    start: string;
    end: string;
    startPlaceholder: string;
    endPlaceholder: string;
    chooseArrival: string;
    chooseDeparture: string;
    previous: string;
    next: string;
    clear: string;
    /** Legend, and what a screen reader hears on such a day. */
    taken: string;
    departureOnly: string;
};

type DateRangePickerProps = {
    start: string;
    end: string;
    onChange: (start: string, end: string) => void;
    /** Nights the owner has marked as taken, as `YYYY-MM-DD`. */
    blockedDates?: string[];
    minValue?: string;
    maxValue?: string;
    /** How many months are drawn at once. Two is the usual shape for a stay. */
    months?: number;
    locale?: string;
    labels: Labels;
    /** The sentence that explains why the month stops. */
    ceilingNote?: (lastDeparture: string) => string;
    startId?: string;
    endId?: string;
    disabled?: boolean;
};

/**
 * Two dates that make a stay, chosen from one calendar.
 *
 * It was two separate pickers before, and the pair could not help disagreeing
 * with itself: the same night was crossed out in the arrival calendar and
 * offered as a plain choice in the departure one, and the days past the last
 * possible departure simply went grey with nothing to say why. One calendar,
 * open at both fields, has no second opinion to give.
 *
 * The whole control turns on a distinction the guest never has to think about
 * and the code cannot get wrong twice: **a stay occupies nights, not days.**
 * From the 3rd to the 5th is two nights — the 3rd and the 4th — so a guesthouse
 * whose 5th is taken is still free for that entire stay. A closed night
 * therefore cannot be an arrival, cannot be passed through, and *can* be the
 * morning you leave. That last one is why the first closed night after the
 * arrival is offered rather than struck out, and everything beyond it is not.
 *
 * Nothing here ever says a day is free. A day with no mark on it is a day the
 * owner has not spoken about, which is a different and much weaker claim.
 */
export function DateRangePicker({
    start,
    end,
    onChange,
    blockedDates,
    minValue,
    maxValue,
    months = 2,
    locale,
    labels,
    ceilingNote,
    startId,
    endId,
    disabled = false,
}: DateRangePickerProps) {
    const blocked = useMemo(() => new Set(blockedDates ?? []), [blockedDates]);
    const startDate = parseDateValue(start);
    const endDate = parseDateValue(end);
    const anchor = startDate ?? parseDateValue(minValue) ?? new Date();

    const [open, setOpen] = useState(false);
    /*
     * Which of the two fields the calendar is currently answering.
     *
     * Without it the control has a dead end: once an arrival is chosen, every
     * day past the first closed night is out of reach as a departure, so
     * somebody who picked the wrong week can no longer click their way out of
     * it. The field they clicked says which question they are answering, which
     * is what the two labels meant all along.
     */
    const [mode, setMode] = useState<'start' | 'end'>('start');
    const [viewMonth, setViewMonth] = useState<Date>(() => startOfMonth(anchor));
    const [focused, setFocused] = useState<Date>(anchor);
    const [hovered, setHovered] = useState<string | null>(null);

    const gridRef = useRef<HTMLDivElement>(null);
    const fieldsRef = useRef<HTMLDivElement>(null);
    const weekdays = useMemo(() => weekdayNames(locale), [locale]);

    /*
     * The last day this stay can end on.
     *
     * The first closed night after the arrival, because the stay cannot run
     * through it — but it can end on its morning. Whichever of that and the
     * published window comes first wins: past the window we have no calendar at
     * all, and an empty calendar is not the same as an empty diary.
     */
    const ceiling = useMemo(() => {
        if (start === '' || mode === 'start') {
            return maxValue;
        }

        const firstClosed = [...blocked].filter((date) => date > start).sort()[0];

        if (firstClosed === undefined) {
            return maxValue;
        }

        return maxValue !== undefined && maxValue < firstClosed ? maxValue : firstClosed;
    }, [blocked, maxValue, mode, start]);

    const ceilingIsClosed = ceiling !== undefined && blocked.has(ceiling);

    function dayState(value: string): DayState {
        if ((minValue !== undefined && value < minValue) || (maxValue !== undefined && value > maxValue)) {
            return 'out';
        }

        // Choosing an arrival, or clicking at or before the one already
        // chosen: either way this click (re)starts the stay, so the only thing
        // that can stop it is the night itself being taken.
        if (mode === 'start' || start === '' || value <= start) {
            return blocked.has(value) ? 'taken' : 'free';
        }

        if (ceiling !== undefined && value > ceiling) {
            return 'out';
        }

        if (blocked.has(value)) {
            return value === ceiling ? 'departure' : 'taken';
        }

        return 'free';
    }

    /** The far end of the band drawn behind the days, hover included. */
    function bandEndValue(): string {
        if (end !== '') {
            return end;
        }

        if (mode === 'start' || start === '' || hovered === null || hovered <= start) {
            return '';
        }

        return ['out', 'taken'].includes(dayState(hovered)) ? '' : hovered;
    }

    const bandEnd = bandEndValue();

    // Arrowing off the edge of the window pages it, so the highlight is never
    // somewhere the reader cannot see.
    useEffect(() => {
        if (!open) {
            return;
        }

        setViewMonth((month) => {
            const first = startOfMonth(month);
            const lastDay = new Date(first.getFullYear(), first.getMonth() + months, 0);

            if (focused < first) {
                return startOfMonth(focused);
            }

            if (focused > lastDay) {
                return new Date(focused.getFullYear(), focused.getMonth() - months + 1, 1);
            }

            return month;
        });

        gridRef.current?.querySelector<HTMLButtonElement>('[data-focused="true"]')?.focus({ preventScroll: true });
    }, [focused, months, open]);

    function openAt(which: 'start' | 'end'): void {
        const at = (which === 'start' ? startDate : (endDate ?? startDate)) ?? anchor;

        // Asked for a departure before there is an arrival, there is only one
        // useful thing to show them.
        setMode(which === 'end' && start !== '' ? 'end' : 'start');
        setViewMonth(startOfMonth(at));
        setFocused(at);
        setOpen(true);
    }

    function pick(value: string): void {
        const state = dayState(value);

        if (state === 'out' || state === 'taken') {
            return;
        }

        // Written through on every click rather than held back until the pair
        // is complete: the fields under the calendar always say what the
        // calendar is showing, even if the reader wanders off mid-choice.
        if (mode === 'start' || start === '' || value <= start) {
            onChange(value, '');
            setMode('end');

            return;
        }

        onChange(start, value);
        setOpen(false);
    }

    function onGridKeyDown(event: React.KeyboardEvent): void {
        const steps: Record<string, number> = { ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7 };

        if (event.key in steps) {
            event.preventDefault();
            setFocused((date) => addDays(date, steps[event.key]));

            return;
        }

        if (event.key == 'PageUp' || event.key == 'PageDown') {
            event.preventDefault();
            setFocused((date) => new Date(date.getFullYear(), date.getMonth() + (event.key == 'PageUp' ? -1 : 1), date.getDate()));
        }
    }

    function trigger(id: string | undefined, value: Date | null, placeholder: string, onOpen: () => void) {
        return (
            <Button
                id={id}
                type="button"
                variant="outline"
                disabled={disabled}
                onClick={onOpen}
                className={cn('h-9 w-full justify-start px-3 text-left font-normal', !value && 'text-muted-foreground')}
            >
                <CalendarIcon className="h-4 w-4 shrink-0 opacity-60" aria-hidden="true" />
                <span className="truncate">{value ? formatDisplayDate(value, locale) : placeholder}</span>
            </Button>
        );
    }

    const visibleMonths = Array.from({ length: months }, (_, index) => new Date(viewMonth.getFullYear(), viewMonth.getMonth() + index, 1));

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverAnchor asChild>
                <div ref={fieldsRef} className="grid grid-cols-2 gap-3">
                    <div className="space-y-2">
                        <Label htmlFor={startId}>{labels.start}</Label>
                        {trigger(startId, startDate, labels.startPlaceholder, () => openAt('start'))}
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor={endId}>{labels.end}</Label>
                        {trigger(endId, endDate, labels.endPlaceholder, () => openAt('end'))}
                    </div>
                </div>
            </PopoverAnchor>

            {/*
                Two months is a tall panel, and it grows again the moment an
                arrival is chosen and the sentence about the last departure
                appears. Without the padding it flips above the field and loses
                its own header off the top of the window.
            */}
            <PopoverContent
                align="start"
                collisionPadding={12}
                /*
                 * The two fields anchor this panel rather than triggering it,
                 * which means Radix counts a click on either of them as a click
                 * outside and closes the calendar underneath the hand that was
                 * switching fields. Clicking a field is the one gesture this
                 * control is built around, so it is exempted here.
                 */
                onInteractOutside={(event) => {
                    if (fieldsRef.current?.contains(event.target as Node)) {
                        event.preventDefault();
                    }
                }}
                className="max-h-[80vh] w-auto overflow-y-auto p-3"
            >
                <div className="flex items-center justify-between gap-2">
                    <Button type="button" variant="ghost" size="icon" className="h-8 w-8" aria-label={labels.previous} onClick={() => setViewMonth((month) => new Date(month.getFullYear(), month.getMonth() - 1, 1))}>
                        <ChevronLeft className="h-4 w-4" />
                    </Button>

                    <p aria-live="polite" className="text-center text-sm font-medium">
                        {mode === 'end' ? labels.chooseDeparture : labels.chooseArrival}
                    </p>

                    <Button type="button" variant="ghost" size="icon" className="h-8 w-8" aria-label={labels.next} onClick={() => setViewMonth((month) => new Date(month.getFullYear(), month.getMonth() + 1, 1))}>
                        <ChevronRight className="h-4 w-4" />
                    </Button>
                </div>

                <div ref={gridRef} onKeyDown={onGridKeyDown} onPointerLeave={() => setHovered(null)} className="mt-3 grid gap-5 sm:grid-cols-2">
                    {visibleMonths.map((month) => (
                        <div key={formatDateValue(month)}>
                            <p className="mb-2 text-center text-xs font-medium first-letter:uppercase">{formatMonthName(month, locale)}</p>

                            <div role="grid" className="grid grid-cols-7 gap-1">
                                {weekdays.map((day) => (
                                    <div key={day} className="flex h-7 w-8 items-center justify-center text-[11px] font-medium text-muted-foreground">
                                        {day}
                                    </div>
                                ))}

                                {monthGrid(month).map((date, index) => {
                                    if (!date) {
                                        return <div key={`empty-${index}`} className="h-8 w-8" />;
                                    }

                                    const value = formatDateValue(date);
                                    const state = dayState(value);
                                    const isStart = value === start;
                                    const isEnd = value === end;
                                    const isBetween = start !== '' && bandEnd !== '' && value > start && value < bandEnd;
                                    const isFocused = value === formatDateValue(focused);

                                    return (
                                        <button
                                            key={value}
                                            type="button"
                                            role="gridcell"
                                            aria-selected={isStart || isEnd}
                                            aria-disabled={state === 'out' || state === 'taken'}
                                            aria-label={
                                                state === 'taken'
                                                    ? `${formatDisplayDate(date, locale)} — ${labels.taken}`
                                                    : state === 'departure'
                                                      ? `${formatDisplayDate(date, locale)} — ${labels.departureOnly}`
                                                      : formatDisplayDate(date, locale)
                                            }
                                            data-focused={isFocused}
                                            // Roving tabindex: one stop for the
                                            // whole calendar, so Tab leaves it
                                            // rather than walking eighty-four days.
                                            tabIndex={isFocused ? 0 : -1}
                                            onClick={() => pick(value)}
                                            onFocus={() => setFocused(date)}
                                            onPointerEnter={() => setHovered(value)}
                                            className={cn(
                                                'flex h-8 w-8 items-center justify-center rounded-md text-sm font-normal',
                                                'focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-hidden',
                                                state === 'free' && !isStart && !isEnd && 'hover:bg-accent hover:text-accent-foreground',
                                                isBetween && 'rounded-none bg-accent text-accent-foreground',
                                                (isStart || isEnd) && 'bg-primary text-primary-foreground',
                                                state === 'out' && 'cursor-not-allowed text-muted-foreground/40',
                                                /*
                                                 * Struck only where somebody
                                                 * decided something. A pale
                                                 * number reads as "not for this
                                                 * field"; a crossed-out one
                                                 * reads as "taken", and only a
                                                 * closed night is.
                                                 */
                                                state === 'taken' && 'cursor-not-allowed text-muted-foreground/60 line-through',
                                                // The morning after a taken
                                                // night: you may leave, you may
                                                // not stay.
                                                state === 'departure' &&
                                                    !isEnd &&
                                                    'text-amber-700 underline decoration-dotted underline-offset-4 hover:bg-accent dark:text-amber-400',
                                            )}
                                        >
                                            {date.getDate()}
                                        </button>
                                    );
                                })}
                            </div>
                        </div>
                    ))}
                </div>

                {blocked.size > 0 && (
                    <div className="mt-3 flex flex-wrap gap-x-4 gap-y-1 border-t pt-3 text-xs text-muted-foreground">
                        <span className="line-through">{labels.taken}</span>
                        <span className="underline decoration-dotted underline-offset-4">{labels.departureOnly}</span>
                    </div>
                )}

                {/*
                    Why the month stops where it does. Without it the greyed
                    tail reads as a broken calendar rather than as a booked one,
                    which is exactly how it read before this sentence existed.
                */}
                {mode === 'end' && ceilingIsClosed && ceiling !== undefined && ceilingNote && (
                    <p className="mt-2 text-xs text-muted-foreground">{ceilingNote(ceiling)}</p>
                )}

                {(start !== '' || end !== '') && (
                    <div className="mt-3 border-t pt-3">
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            className="w-full"
                            onClick={() => {
                                onChange('', '');
                                setOpen(false);
                            }}
                        >
                            {labels.clear}
                        </Button>
                    </div>
                )}
            </PopoverContent>
        </Popover>
    );
}
