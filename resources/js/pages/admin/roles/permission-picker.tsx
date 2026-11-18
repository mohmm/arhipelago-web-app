import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Search } from 'lucide-react';
import { useMemo, useState } from 'react';

import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { groupTitle } from './labels';
import type { PermissionGroups } from './labels';

interface PermissionPickerProps {
    groups: PermissionGroups;
    selected: string[];
    onChange: (next: string[]) => void;
    disabled?: boolean;
}

/**
 * Checkbox list of every assignable permission, grouped by the policy it
 * belongs to, with a per-group toggle and a filter for the longer lists.
 */
export default function PermissionPicker({ groups, selected, onChange, disabled = false }: PermissionPickerProps) {
    const { t } = useLaravelReactI18n();
    const [search, setSearch] = useState('');

    const selectedSet = useMemo(() => new Set(selected), [selected]);

    const visibleGroups = useMemo(() => {
        const term = search.trim().toLowerCase();

        if (term === '') {
            return Object.entries(groups);
        }

        return Object.entries(groups)
            .map(([group, names]) => {
                const matches =
                    groupTitle(group, t).toLowerCase().includes(term)
                        ? names
                        : names.filter((name) => name.toLowerCase().includes(term));

                return [group, matches] as const;
            })
            .filter(([, names]) => names.length > 0);
    }, [groups, search, t]);

    function toggle(name: string, checked: boolean) {
        onChange(checked ? [...selected, name] : selected.filter((item) => item !== name));
    }

    function toggleGroup(names: string[], checked: boolean) {
        onChange(
            checked
                ? Array.from(new Set([...selected, ...names]))
                : selected.filter((item) => !names.includes(item)),
        );
    }

    return (
        <div className="flex min-h-0 flex-1 flex-col gap-4">
            <div className="relative">
                <Search className="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    value={search}
                    onChange={(event) => setSearch(event.target.value)}
                    placeholder={t('Filter permissions')}
                    className="pl-9"
                    disabled={disabled}
                />
            </div>

            <div className="min-h-0 flex-1 space-y-6 overflow-y-auto pr-1">
                {visibleGroups.length === 0 ? (
                    <p className="py-8 text-center text-sm text-muted-foreground">
                        {t('No permissions match your search.')}
                    </p>
                ) : (
                    visibleGroups.map(([group, names]) => {
                        const selectedCount = names.filter((name) => selectedSet.has(name)).length;
                        const allSelected = selectedCount === names.length;

                        return (
                            <div key={group} className="space-y-3">
                                <div className="flex items-center justify-between gap-3 border-b pb-2">
                                    <div className="flex items-center gap-2">
                                        <Checkbox
                                            id={`group-${group}`}
                                            checked={allSelected ? true : selectedCount > 0 ? 'indeterminate' : false}
                                            onCheckedChange={(checked) => toggleGroup(names, checked === true)}
                                            disabled={disabled}
                                        />
                                        <Label htmlFor={`group-${group}`} className="text-sm font-semibold">
                                            {groupTitle(group, t)}
                                        </Label>
                                    </div>
                                    <span className="text-xs text-muted-foreground tabular-nums">
                                        {selectedCount}/{names.length}
                                    </span>
                                </div>

                                <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                    {names.map((name) => (
                                        <div key={name} className="flex items-center gap-2">
                                            <Checkbox
                                                id={`permission-${name}`}
                                                checked={selectedSet.has(name)}
                                                onCheckedChange={(checked) => toggle(name, checked === true)}
                                                disabled={disabled}
                                            />
                                            <Label
                                                htmlFor={`permission-${name}`}
                                                className="cursor-pointer font-mono text-xs font-normal break-all"
                                            >
                                                {name}
                                            </Label>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        );
                    })
                )}
            </div>
        </div>
    );
}
