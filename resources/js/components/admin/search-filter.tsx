import { router } from '@inertiajs/react';
import { Search } from 'lucide-react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import { Input } from '@/components/ui/input';

type Props = {
    url: string;
    filters: Record<string, string | undefined>;
    placeholder: string;
};

/**
 * A search box that keeps the other filters of the list as they are.
 */
export default function SearchFilter({ url, filters, placeholder }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');

    const submit = (event: FormEvent) => {
        event.preventDefault();
        router.get(url, Object.fromEntries(Object.entries({ ...filters, search }).filter(([, value]) => value != null && value != '')), {
            preserveState: true,
        });
    };

    return (
        <form onSubmit={submit} className="relative min-w-64 flex-1">
            <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
            <Input value={search} onChange={(event) => setSearch(event.target.value)} placeholder={placeholder} className="pl-9" />
        </form>
    );
}
