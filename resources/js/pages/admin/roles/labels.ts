/**
 * Permission names grouped by the policy they belong to. The keys come from the
 * server's PermissionCatalog: a model key such as `point_of_interest`, or
 * `sections` for the permissions that gate navigation rather than a record.
 */
export type PermissionGroups = Record<string, string[]>;

type Translate = (key: string) => string;

/**
 * Turn a snake or kebab identifier into a readable title, e.g.
 * `point_of_interest` becomes `Point of interest`.
 */
export function humanize(value: string): string {
    const words = value.replace(/[_-]+/g, ' ').trim();

    return words.charAt(0).toUpperCase() + words.slice(1);
}

/**
 * The translated heading for a permission group, falling back to the humanized
 * key when no translation exists for it yet.
 */
export function groupTitle(group: string, t: Translate): string {
    return t(humanize(group));
}

/**
 * The translated display name for a role, e.g. `super_admin` → `Super admin`.
 */
export function roleTitle(name: string, t: Translate): string {
    return t(humanize(name));
}
