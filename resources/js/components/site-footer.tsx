import { Link, usePage } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { LifeBuoy, Mail, MapPin, Phone } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import AppearanceToggleDropdown from '@/components/appearance-dropdown';
import LanguageSwitcher from '@/components/language-switcher';
import { StructuralFundsCaption, StructuralFundsEmblems } from '@/components/structural-funds';
import { useLocalizedUrl } from '@/lib/locale';
import { ANPC_SAL_URL, EXTERNAL_LINK_PROPS } from '@/lib/site';
import type { SharedData } from '@/types';

/**
 * The funding acknowledgement the grant requires, quoted exactly: the funding
 * authority fixes the wording, so it is neither paraphrased nor shortened.
 */
const EU_FUNDING_NOTICE =
    'Funded by the European Union. Views and opinions expressed are however those of the author(s) only and do not necessarily reflect those of the European Union or the European Health and Digital Executive Agency (HADEA). Neither the European Union nor the granting authority can be held responsible for them.';

/**
 * The website's footer, laid out like smartsense.ro's: who we are and the legal
 * documents; then the funding acknowledgements and the ANPC pictogram, which
 * are obligations rather than badges; then who built the product, the trader's
 * details, the language and the theme.
 */
export function SiteFooter() {
    const { t } = useLaravelReactI18n();
    const url = useLocalizedUrl();
    const { company } = usePage<SharedData>().props;

    const legal = [
        { title: t('Terms of Service'), href: url('/terms') },
        { title: t('Privacy Policy'), href: url('/privacy') },
        { title: t('Cookie Policy'), href: url('/cookies') },
        { title: t('AI Transparency Notice'), href: url('/ai') },
    ];

    const contactLinkClass = 'flex items-center gap-2.5 transition-colors hover:text-foreground';

    return (
        <footer className="border-t bg-muted/30">
            <div className="mx-auto max-w-6xl px-4 py-12 sm:px-6">
                <div className="grid gap-10 sm:grid-cols-[minmax(0,1fr)_auto] sm:gap-16">
                    <div>
                        <div className="flex items-center gap-2.5">
                            <AppLogo />
                        </div>
                        <p className="mt-4 max-w-md text-sm leading-relaxed text-muted-foreground">
                            {t(
                                'Smartsense is a travel app that helps travellers discover Romania: points of interest, ready-made experiences and Maya, an AI assistant.',
                            )}
                        </p>

                        <address className="mt-6 space-y-2.5 text-sm text-muted-foreground not-italic">
                            <a href={`mailto:${company.email}`} className={contactLinkClass}>
                                <Mail className="size-4 shrink-0" />
                                {company.email}
                            </a>
                            <a href={`mailto:${company.support_email}`} className={contactLinkClass}>
                                <LifeBuoy className="size-4 shrink-0" />
                                {company.support_email}
                            </a>
                            <a href={`tel:${company.phone.replace(/\s/g, '')}`} className={contactLinkClass}>
                                <Phone className="size-4 shrink-0" />
                                {company.phone}
                            </a>
                            <span className="flex items-start gap-2.5">
                                <MapPin className="mt-0.5 size-4 shrink-0" />
                                {company.address}, {company.country}
                            </span>
                        </address>
                    </div>

                    <nav aria-label={t('Legal')}>
                        <h3 className="text-sm font-semibold text-foreground">{t('Legal')}</h3>
                        <ul className="mt-4 space-y-3">
                            {legal.map((link) => (
                                <li key={link.href}>
                                    <Link href={link.href} className="text-sm text-muted-foreground transition-colors hover:text-foreground">
                                        {link.title}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </nav>
                </div>

                {/*
                 * Two grants are acknowledged, each emblem above its own notice, so a
                 * reader can tell which grant paid for what. The ANPC pictogram is the
                 * alternative-dispute-resolution link every Romanian trader must show.
                 */}
                <div className="mt-12 grid gap-10 border-t pt-8 lg:grid-cols-[minmax(0,1fr)_auto_auto]">
                    <div className="flex flex-col gap-4">
                        {/*
                         * The supplied emblem has white lettering, invisible on a light page,
                         * so each theme gets the variant it can be read on. `self-start` keeps
                         * the flag from being stretched when the footer stacks: the EU rules
                         * forbid distorting it.
                         */}
                        <img
                            src="/images/eu-co-funded-dark.png"
                            alt={t('Co-funded by the European Union')}
                            width={4247}
                            height={891}
                            loading="lazy"
                            className="h-10 w-auto max-w-full shrink-0 self-start object-contain dark:hidden"
                        />
                        <img
                            src="/images/EN-Co-Funded-by-the-EU_POS.png"
                            alt={t('Co-funded by the European Union')}
                            width={4247}
                            height={891}
                            loading="lazy"
                            className="hidden h-10 w-auto max-w-full shrink-0 self-start object-contain dark:block"
                        />
                        <p className="max-w-2xl text-xs leading-relaxed text-muted-foreground">{t(EU_FUNDING_NOTICE)}</p>
                    </div>

                    <div className="flex flex-col gap-4">
                        <StructuralFundsEmblems />
                        <StructuralFundsCaption className="max-w-xs" />
                    </div>

                    <a
                        href={ANPC_SAL_URL}
                        {...EXTERNAL_LINK_PROPS}
                        className="shrink-0 self-start rounded-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                    >
                        <img
                            src="/images/anpc-sal.png"
                            alt={t('ANPC — Alternative dispute resolution')}
                            width={500}
                            height={124}
                            loading="lazy"
                            className="h-auto w-[250px] max-w-full"
                        />
                    </a>
                </div>

                <div className="mt-8 flex flex-col gap-6 border-t pt-8 sm:flex-row sm:items-center sm:justify-between">
                    <div className="space-y-3">
                        <a
                            href={company.website}
                            {...EXTERNAL_LINK_PROPS}
                            className="inline-flex flex-col gap-1.5 rounded-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                        >
                            <span className="text-xs text-muted-foreground">{t('A product developed by')}</span>
                            {/* Navy artwork, knocked out to white on a dark page. */}
                            <img
                                src="/images/Altfactor-logo.png"
                                alt={company.legal_name}
                                width={1471}
                                height={256}
                                loading="lazy"
                                className="h-6 w-auto self-start object-contain dark:brightness-0 dark:invert"
                            />
                        </a>

                        <p className="text-sm text-muted-foreground">
                            © {new Date().getFullYear()} Smartsense. {t('All rights reserved.')}
                        </p>
                        {/* Law 365/2002 art. 5 wants the trader identified on every page, not only in the Terms. */}
                        <p className="text-xs text-muted-foreground">
                            {company.legal_name} · {t('Trade register')} {company.trade_register} · {t('Company ID')} {company.cui} · {t('VAT ID')}{' '}
                            {company.vat_id}
                            {company.share_capital != '' && (
                                <>
                                    {' '}
                                    · {t('Share capital')} {company.share_capital}
                                </>
                            )}
                        </p>
                    </div>

                    <div className="flex items-center gap-2">
                        <LanguageSwitcher />
                        <AppearanceToggleDropdown />
                    </div>
                </div>
            </div>
        </footer>
    );
}
