import { CalendarIcon, ChevronLeft, ChevronRight } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';

import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';

type DatePickerProps = {
    value?: string;
    onChange: (value: string) => void;
    minValue?: string;
    maxValue?: string;
    /** Individual days that cannot be chosen, as `YYYY-MM-DD`. */
    disabledDates?: string[];
    /** BCP-47 tag for the month and weekday names. Falls back to the browser's. */
    locale?: string;
    placeholder?: string;
    resetLabel?: string;
    previousLabel?: string;
    nextLabel?: string;
    /** Read out on a day the calendar refuses, so it is not silently inert. */
    unavailableLabel?: string;
    className?: string;
    id?: string;
    disabled?: boolean;
    'aria-label'?: string;
};

function pad(value: number): string {
    return String(value).padStart(2, '0');
}

export function formatDateValue(date: Date): string {
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
}

export function parseDateValue(value?: string): Date | null {
    if (!value) {
        return null;
    }

    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value);

    if (!match) {
        return null;
    }

    const year = Number(match[1]);
    const month = Number(match[2]) - 1;
    const day = Number(match[3]);
    const date = new Date(year, month, day);

    // `new Date(2026, 1, 31)` silently becomes the 3rd of March. Reading the
    // parts back is what turns an impossible date into null instead of a
    // plausible wrong one.
    if (date.getFullYear() != year || date.getMonth() != month || date.getDate() != day) {
        return null;
    }

    return date;
}

export function startOfMonth(date: Date): Date {
    return new Date(date.getFullYear(), date.getMonth(), 1);
}

export function addDays(date: Date, days: number): Date {
    return new Date(date.getFullYear(), date.getMonth(), date.getDate() + days);
}

function isSameDay(first: Date | null, second: Date | null): boolean {
    return (
        first != null &&
        second != null &&
        first.getFullYear() == second.getFullYear() &&
        first.getMonth() == second.getMonth() &&
        first.getDate() == second.getDate()
    );
}

export function compareDates(first: Date, second: Date): number {
    const firstValue = new Date(first.getFullYear(), first.getMonth(), first.getDate()).getTime();
    const secondValue = new Date(second.getFullYear(), second.getMonth(), second.getDate()).getTime();

    if (firstValue < secondValue) {
        return -1;
    }

    return firstValue > secondValue ? 1 : 0;
}

export function isDateValueAfter(first?: string, second?: string): boolean {
    const firstDate = parseDateValue(first);
    const secondDate = parseDateValue(second);

    return firstDate != null && secondDate != null && compareDates(firstDate, secondDate) > 0;
}

/**
 * The days of one month, Monday first, padded to six whole rows.
 *
 * Monday rather than Sunday everywhere, and not derived from the locale:
 * `Intl.Locale.getWeekInfo` is still missing in Firefox, so reading it would
 * make the same page start its week on a different day in different browsers —
 * the exact inconsistency this component exists to remove. Monday is right for
 * Romania and is the ISO default.
 *
 * Six rows always, so the popover does not change height as the months are
 * paged through and the button under the cursor does not move out from under it.
 */
export function monthGrid(monthDate: Date): (Date | null)[] {
    const year = monthDate.getFullYear();
    const month = monthDate.getMonth();
    const leading = (new Date(year, month, 1).getDay() + 6) % 7;
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const days: (Date | null)[] = Array.from({ length: leading }, () => null);

    for (let day = 1; day <= daysInMonth; day += 1) {
        days.push(new Date(year, month, day));
    }

    while (days.length < 42) {
        days.push(null);
    }

    return days;
}

/**
 * Short weekday names in the reader's own language, Monday first.
 *
 * Built from a real week rather than a hardcoded list, so Romanian reads
 * "lun mar mie" without the application shipping a table of its own.
 */
export function weekdayNames(locale?: string): string[] {
    const formatter = new Intl.DateTimeFormat(locale, { weekday: 'short' });
    // 5 January 2026 was a Monday. Any Monday would do.
    const monday = new Date(2026, 0, 5);

    return Array.from({ length: 7 }, (_, index) => formatter.format(addDays(monday, index)));
}

export function formatMonthName(date: Date, locale?: string): string {
    return date.toLocaleDateString(locale, { month: 'long', year: 'numeric' });
}

export function formatDisplayDate(date: Date, locale?: string): string {
    return date.toLocaleDateString(locale, { day: 'numeric', month: 'short', year: 'numeric' });
}

/**
 * One date, chosen from a calendar that looks the same everywhere.
 *
 * A native `<input type="date">` is drawn by the operating system: Safari shows
 * a stepper, Chrome shows its own panel, and Android shows a full-screen dial.
 * Three shapes, three sizes and three date formats on one form. This is drawn
 * by the application, so a Romanian owner sees a Romanian month name and the
 * same control on their phone as on the reception desk.
 *
 * It also does the thing a native picker cannot: refuse individual days.
 * `disabledDates` is how a guest is stopped from asking for nights the owner
 * has already closed — greyed rather than hidden, because a gap in a month
 * grid reads as a rendering fault.
 */
export function DatePicker({
    value,
    onChange,
    minValue,
    maxValue,
    disabledDates,
    locale,
    placeholder = 'dd.mm.yyyy',
    resetLabel = 'Reset',
    previousLabel = 'Previous',
    nextLabel = 'Next',
    unavailableLabel = 'Unavailable',
    className,
    id,
    disabled = false,
    'aria-label': ariaLabel,
}: DatePickerProps) {
    const selectedDate = parseDateValue(value);
    const minDate = parseDateValue(minValue);
    const maxDate = parseDateValue(maxValue);
    const anchorDate = selectedDate ?? minDate ?? maxDate ?? new Date();

    const [open, setOpen] = useState(false);
    const [currentMonth, setCurrentMonth] = useState<Date>(() => startOfMonth(anchorDate));
    const [focused, setFocused] = useState<Date>(anchorDate);

    const gridRef = useRef<HTMLDivElement>(null);
    const days = useMemo(() => monthGrid(currentMonth), [currentMonth]);
    const weekdays = useMemo(() => weekdayNames(locale), [locale]);
    const blocked = useMemo(() => new Set(disabledDates ?? []), [disabledDates]);
    const today = new Date();

    // The keyboard moves a highlight rather than the selection, so arrowing
    // past the end of a month has to page the grid with it.
    useEffect(() => {
        if (!open) {
            return;
        }

        setCurrentMonth((month) =>
            month.getFullYear() == focused.getFullYear() && month.getMonth() == focused.getMonth() ? month : startOfMonth(focused),
        );

        gridRef.current?.querySelector<HTMLButtonElement>('[data-focused="true"]')?.focus({ preventScroll: true });
    }, [focused, open]);

    function handleOpenChange(nextOpen: boolean): void {
        if (nextOpen) {
            setCurrentMonth(startOfMonth(anchorDate));
            setFocused(anchorDate);
        }

        setOpen(nextOpen);
    }

    function changeMonth(offset: number): void {
        setCurrentMonth((month) => new Date(month.getFullYear(), month.getMonth() + offset, 1));
    }

    /**
     * Outside the range this field accepts — usually already in the past.
     *
     * Kept apart from a blocked day because the two mean different things and
     * must not look alike. A day gone by is unremarkable; a day somebody closed
     * is a decision, and the sentence beside this control says so. Drawing both
     * the same way would make that sentence a lie about half the month.
     */
    function isOutOfRange(date: Date): boolean {
        return (minDate != null && compareDates(date, minDate) < 0) || (maxDate != null && compareDates(date, maxDate) > 0);
    }

    function isDateDisabled(date: Date): boolean {
        return isOutOfRange(date) || blocked.has(formatDateValue(date));
    }

    function selectDate(date: Date): void {
        if (isDateDisabled(date)) {
            return;
        }

        onChange(formatDateValue(date));
        setOpen(false);
    }

    function onGridKeyDown(event: React.KeyboardEvent): void {
        const steps: Record<string, number> = {
            ArrowLeft: -1,
            ArrowRight: 1,
            ArrowUp: -7,
            ArrowDown: 7,
        };

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

    return (
        <Popover open={open} onOpenChange={handleOpenChange}>
            <PopoverTrigger asChild>
                <Button
                    id={id}
                    type="button"
                    variant="outline"
                    disabled={disabled}
                    aria-label={ariaLabel}
                    className={cn('h-9 w-full justify-start px-3 text-left font-normal', !selectedDate && 'text-muted-foreground', className)}
                >
                    <CalendarIcon className="h-4 w-4 shrink-0 opacity-60" aria-hidden="true" />
                    <span className="truncate">{selectedDate ? formatDisplayDate(selectedDate, locale) : placeholder}</span>
                </Button>
            </PopoverTrigger>

            <PopoverContent align="start" className="w-auto p-3">
                <div className="flex items-center justify-between gap-2">
                    <Button type="button" variant="ghost" size="icon" className="h-8 w-8" onClick={() => changeMonth(-1)} aria-label={previousLabel}>
                        <ChevronLeft className="h-4 w-4" />
                    </Button>
                    <div aria-live="polite" className="min-w-36 text-center text-sm font-medium first-letter:uppercase">
                        {formatMonthName(currentMonth, locale)}
                    </div>
                    <Button type="button" variant="ghost" size="icon" className="h-8 w-8" onClick={() => changeMonth(1)} aria-label={nextLabel}>
                        <ChevronRight className="h-4 w-4" />
                    </Button>
                </div>

                <div ref={gridRef} role="grid" onKeyDown={onGridKeyDown} className="mt-3 grid grid-cols-7 gap-1">
                    {weekdays.map((day) => (
                        <div key={day} className="flex h-7 items-center justify-center text-[11px] font-medium text-muted-foreground">
                            {day}
                        </div>
                    ))}

                    {days.map((date, index) => {
                        if (!date) {
                            return <div key={`empty-${index}`} className="h-8 w-8" />;
                        }

                        const isUnavailable = isDateDisabled(date);
                        const isClosed = isUnavailable && !isOutOfRange(date);
                        const isSelected = isSameDay(date, selectedDate);
                        const isFocused = isSameDay(date, focused);

                        return (
                            <button
                                key={formatDateValue(date)}
                                type="button"
                                role="gridcell"
                                aria-selected={isSelected}
                                aria-disabled={isUnavailable}
                                aria-label={isClosed ? `${formatDisplayDate(date, locale)} — ${unavailableLabel}` : formatDisplayDate(date, locale)}
                                data-focused={isFocused}
                                // Roving tabindex: one stop for the whole grid,
                                // so Tab leaves the calendar instead of walking
                                // through forty-two days.
                                tabIndex={isFocused ? 0 : -1}
                                onClick={() => selectDate(date)}
                                onFocus={() => setFocused(date)}
                                className={cn(
                                    'flex h-8 w-8 items-center justify-center rounded-md text-sm font-normal',
                                    'focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-hidden',
                                    isSelected && 'bg-primary text-primary-foreground',
                                    !isSelected && !isUnavailable && 'hover:bg-accent hover:text-accent-foreground',
                                    !isSelected && isSameDay(date, today) && 'border border-input',
                                    isUnavailable && 'cursor-not-allowed text-muted-foreground/50',
                                    /*
                                     * Struck through only when somebody closed
                                     * it. A pale number reads as "not for this
                                     * field"; a crossed-out one reads as a
                                     * decision, and only a closed day is one.
                                     */
                                    isClosed && 'line-through',
                                )}
                            >
                                {date.getDate()}
                            </button>
                        );
                    })}
                </div>

                {selectedDate && (
                    <div className="mt-3 border-t pt-3">
                        <Button type="button" variant="ghost" size="sm" className="w-full" onClick={() => { onChange(''); setOpen(false); }}>
                            {resetLabel}
                        </Button>
                    </div>
                )}
            </PopoverContent>
        </Popover>
    );
}
