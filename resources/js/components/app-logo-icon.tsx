import type { ImgHTMLAttributes } from 'react';

type AppLogoIconProps = Omit<ImgHTMLAttributes<HTMLImageElement>, 'src' | 'alt'>;

/**
 * The brand symbol on its own, without the name.
 *
 * The full logo is a wide image with the name and a tagline, so squeezing it
 * into a square slot left the name a few pixels tall and unreadable. The name
 * is set in text beside this instead, which follows the theme and can actually
 * be read.
 *
 * Decorative by default: every place that renders it also renders the name, so
 * an alt text here would only make a screen reader say the name twice.
 */
export default function AppLogoIcon({ className, ...props }: AppLogoIconProps) {
    return <img src="/images/logo-mark.png" alt="" className={className} {...props} />;
}
