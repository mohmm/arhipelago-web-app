import { Head, usePage } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Languages, Map, MapPin, MonitorSmartphone, Route, Sparkles, Users } from 'lucide-react';
import type { ComponentType } from 'react';
import WebsiteLayout from '@/layouts/website-layout';
import type { SharedData } from '@/types';

type Feature = { icon: ComponentType<{ className?: string }>; title: string; text: string };

type Screen = { src: string; alt: string };

/**
 * Two screens of the app side by side, level and at the same size, as the
 * app's design shows them. `items-start` keeps each phone at its own height:
 * a flex row stretches its items, which once drew one phone taller than the
 * other.
 */
function AppScreens({ screens, className = '' }: { screens: Screen[]; className?: string }) {
    return (
        <div className={`flex items-start justify-center gap-4 sm:gap-6 ${className}`}>
            {screens.map((screen) => (
                <img key={screen.src} src={screen.src} alt={screen.alt} width={600} height={1257} className="w-1/2 max-w-64 drop-shadow-xl" />
            ))}
        </div>
    );
}

/**
 * The website's home page: what the app is and what it does, as
 * the app's design presents it.
 */
export default function Welcome() {
    const { t } = useLaravelReactI18n();
    const { name } = usePage<SharedData>().props;

    const features: Feature[] = [
        {
            icon: MapPin,
            title: t('Points of interest'),
            text: t('Castles, fortresses, museums and nature, with photos, a description and their place on the map.'),
        },
        { icon: Route, title: t('Experiences'), text: t('Day routes with stops at the points of interest, on real roads.') },
        { icon: Map, title: t('Map'), text: t('Points of interest and experiences together, on the same map.') },
        { icon: Sparkles, title: t('Maya, the AI assistant'), text: t('She recommends what is worth seeing and answers questions about any place.') },
        { icon: Users, title: t('Community'), text: t('Photos, likes, comments and reviews from other travellers.') },
        {
            icon: Languages,
            title: t('Account and languages'),
            text: t('Your own account and profile, in three languages: Romanian, English and Arabic.'),
        },
    ];

    return (
        <WebsiteLayout>
            <Head title={t('The mobile app')}>
                <meta
                    name="description"
                    content={t(
                        ':app is a travel app that helps travellers discover Romania: points of interest, ready-made experiences and Maya, an AI assistant.',
                        { app: name },
                    )}
                />
            </Head>

            <section className="overflow-hidden">
                <div className="mx-auto grid max-w-6xl items-center gap-12 px-4 py-14 sm:px-6 lg:grid-cols-2 lg:py-20">
                    <div className="grid gap-6">
                        <p className="font-display text-sm font-semibold text-primary">{name}</p>
                        <h1 className="text-5xl font-bold tracking-tight sm:text-6xl">{t('The mobile app')}</h1>
                        <p className="text-lg leading-relaxed text-muted-foreground">
                            {t(
                                ':app is a travel app. It helps travellers discover Romania: points of interest, ready-made experiences and Maya, an AI assistant who recommends what is worth seeing. All on the same map, with photos and impressions from other travellers.',
                                { app: name },
                            )}
                        </p>
                    </div>
                    <AppScreens
                        screens={[
                            { src: '/images/app/points-of-interest.webp', alt: t('The list of points of interest in the app') },
                            { src: '/images/app/map.webp', alt: t('The map in the app') },
                        ]}
                    />
                </div>
            </section>

            <section className="bg-muted/30">
                <div className="mx-auto grid max-w-6xl gap-10 px-4 py-16 sm:px-6">
                    <div className="grid gap-2">
                        <p className="text-sm font-semibold text-primary">{t('In short')}</p>
                        <h2 className="text-4xl font-bold tracking-tight">{t('What the app does')}</h2>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {features.map((feature) => (
                            <div key={feature.title} className="grid content-start gap-3 rounded-2xl border bg-card p-6">
                                <feature.icon className="size-7 text-primary" />
                                <h3 className="text-lg font-semibold">{feature.title}</h3>
                                <p className="text-muted-foreground">{feature.text}</p>
                            </div>
                        ))}
                    </div>

                    <div className="flex items-start gap-4 rounded-2xl border border-primary/20 bg-accent p-6">
                        <MonitorSmartphone className="size-7 shrink-0 text-primary" />
                        <div className="grid gap-1">
                            <h3 className="text-lg font-semibold">{t('Web admin panel')}</h3>
                            <p className="text-muted-foreground">{t('All the app’s content is managed in one place, on the web.')}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section>
                <div className="mx-auto grid max-w-6xl items-center gap-12 px-4 py-16 sm:px-6 lg:grid-cols-2">
                    <AppScreens
                        className="lg:order-2"
                        screens={[
                            { src: '/images/app/experiences.webp', alt: t('The experiences in the app') },
                            { src: '/images/app/maya.webp', alt: t('A conversation with Maya in the app') },
                        ]}
                    />
                    <div className="grid gap-6">
                        <h2 className="text-4xl font-bold tracking-tight">{t('Routes ready to go, and a guide who knows them')}</h2>
                        <p className="text-lg leading-relaxed text-muted-foreground">
                            {t(
                                'An experience is a day route with stops at points of interest, measured on real roads: classic, with the family, car-free or in winter. Maya recommends them, answers questions about any of them, and says plainly when she does not know something instead of making it up.',
                            )}
                        </p>
                    </div>
                </div>
            </section>
        </WebsiteLayout>
    );
}
