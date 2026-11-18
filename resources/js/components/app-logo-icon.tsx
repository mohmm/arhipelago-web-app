import type { ImgHTMLAttributes } from 'react';

type AppLogoIconProps = Omit<ImgHTMLAttributes<HTMLImageElement>, 'src' | 'alt'>;

/**
 * The mark on its own, without the wordmark baked in.
 *
 * The full lockup is a wide image with "SMARTSENSE" set in black, so squeezing
 * it into a square slot left the name a few pixels tall and invisible, and the
 * black letters vanished outright in dark mode. The name is set in text beside
 * this instead, which follows the theme and can actually be read.
 *
 * Decorative by default: every place that renders it also renders the name, so
 * an alt text here would only make a screen reader say "Smartsense" twice.
 */
export default function AppLogoIcon({ className, ...props }: AppLogoIconProps) {
    return <img src="/images/smartsense-logo.png" alt="" className={className} {...props} />;
}
