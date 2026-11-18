import { useLaravelReactI18n } from 'laravel-react-i18n';

import { EXTERNAL_LINK_PROPS, STRUCTURAL_FUNDS } from '@/lib/site';

/**
 * The three emblems the Romanian structural-funds programme requires, in the
 * order and with the captions its own identity manual sets.
 *
 * On a light page they sit bare, like every other mark in the footer. On a dark
 * one they get a white plaque, because two of the three are dark artwork on
 * transparency: the Guvernul României seal dims to almost nothing and the words
 * "Instrumente Structurale 2014–2020" disappear outright, which would leave the
 * programme's own name unreadable on the emblem that carries it. The emblem
 * rules ask for a plain, uncluttered ground and forbid degrading the artwork,
 * so the plaque is the compliant answer rather than a decorative one — and it
 * appears only where it is doing that work.
 */
const EMBLEMS = [
    { src: '/images/UE-logo.png', width: 78, height: 64, name: 'European Union', site: 'europa.eu', href: 'https://europa.eu' },
    { src: '/images/GOV-RO-logo.png', width: 64, height: 64, name: 'Government of Romania', site: 'gov.ro', href: 'https://gov.ro' },
    {
        src: '/images/Fonduri-UE-logo.png',
        width: 68,
        height: 64,
        name: 'Structural Instruments 2014–2020',
        site: 'fonduri-ue.ro',
        href: 'https://fonduri-ue.ro',
    },
] as const;

/**
 * Two sizes, because the two places this appears are doing different jobs. The
 * home page is making an argument to someone who was telephoned an hour ago and
 * is checking whether Smartsense is real; the footer is discharging an
 * obligation on every other page.
 */
const SIZES = {
    default: { image: 'h-10', gap: 'gap-6', site: 'text-[11px]' },
    large: { image: 'h-20', gap: 'gap-8 sm:gap-10', site: 'text-xs' },
} as const;

export function StructuralFundsEmblems({ size = 'default' }: { size?: keyof typeof SIZES }) {
    const { t } = useLaravelReactI18n();
    const scale = SIZES[size];

    return (
        <div className={`flex shrink-0 items-end rounded-lg dark:bg-white dark:px-5 dark:py-4 ${scale.gap}`}>
            {EMBLEMS.map((emblem) => (
                <a
                    key={emblem.site}
                    href={emblem.href}
                    {...EXTERNAL_LINK_PROPS}
                    className="flex flex-col items-center gap-2 rounded-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                >
                    <img
                        src={emblem.src}
                        alt={t(emblem.name)}
                        width={emblem.width}
                        height={emblem.height}
                        loading="lazy"
                        className={`w-auto object-contain ${scale.image}`}
                    />
                    {/* Inside the plaque once the page is dark, so the site
                        names take a colour that works on white rather than the
                        muted token, which would be light-on-light there. */}
                    <span className={`leading-none whitespace-nowrap text-muted-foreground dark:text-neutral-500 ${scale.site}`}>{emblem.site}</span>
                </a>
            ))}
        </div>
    );
}

/**
 * The sentence that says what the emblems are doing there.
 *
 * A row of institutional logos with no caption is a claim nobody can check, and
 * the programme's rules ask for the funding to be named rather than implied.
 * The project's own title and contract number are printed as soon as they are
 * filled in; until then the caption says only what the third emblem already
 * says on its face, which cannot be wrong.
 *
 * It travels with the emblems everywhere they go. The footer carries a second,
 * unrelated funding notice a few centimetres away, and an unlabelled strip of
 * emblems beside it would leave a reader unable to tell which grant paid for
 * what — or read as one very heavily funded claim instead of two specific ones.
 */
export function StructuralFundsCaption({ className = '' }: { className?: string }) {
    const { t } = useLaravelReactI18n();

    return (
        <div className={`text-xs leading-relaxed text-muted-foreground ${className}`}>
            <p>{t('Project co-financed through the European Structural Instruments 2014–2020.')}</p>

            {STRUCTURAL_FUNDS.projectName != '' && (
                <p className="mt-1.5">
                    {STRUCTURAL_FUNDS.projectName}
                    {STRUCTURAL_FUNDS.contractNumber != '' && <> · {STRUCTURAL_FUNDS.contractNumber}</>}
                </p>
            )}
        </div>
    );
}

/**
 * The home page's band: the argument on the left, the emblems on the right,
 * across the same width as everything else on the page.
 */
export function StructuralFundsNotice({ className = '' }: { className?: string }) {
    const { t } = useLaravelReactI18n();

    return (
        <div className={`flex flex-col items-center gap-6 lg:flex-row lg:items-center lg:justify-between lg:gap-12 ${className}`}>
            <div className="max-w-2xl text-center lg:text-left">
                <p className="text-base font-semibold tracking-tight text-foreground">{t('European and national funding')}</p>
                <StructuralFundsCaption className="mt-1.5 text-sm" />
            </div>

            <StructuralFundsEmblems size="large" />
        </div>
    );
}
