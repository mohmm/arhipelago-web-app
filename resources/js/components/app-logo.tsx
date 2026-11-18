import AppLogoIcon from './app-logo-icon';

/**
 * The mark plus the name. Used in the site header, the footer and the back
 * office sidebar, where the sidebar hides the text itself when it collapses.
 */
export default function AppLogo() {
    return (
        <>
            <AppLogoIcon className="size-8 shrink-0 rounded-lg object-contain" />
            <span className="truncate text-base font-semibold tracking-tight">Smartsense</span>
        </>
    );
}
