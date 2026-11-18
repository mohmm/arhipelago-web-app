<?php

namespace App\Enums;

/**
 * The licences a place's photo may have. The app shows photos without a
 * credit line, so only licences that ask for none are allowed.
 */
enum PhotoLicense: string
{
    /** Taken by our team, or bought with the right to publish it without a credit. */
    case Own = 'own';

    case PublicDomain = 'public-domain';

    /** Creative Commons Zero: the author waived every right. */
    case Cc0 = 'cc0';

    public function label(?string $locale = null): string
    {
        return __(match ($this) {
            self::Own => 'Our own photo',
            self::PublicDomain => 'Public domain',
            self::Cc0 => 'CC0 (no rights reserved)',
        }, locale: $locale);
    }

    /**
     * The licence a Wikimedia Commons file states, when it needs no credit.
     */
    public static function fromCommons(string $licence): ?self
    {
        return match (true) {
            str_starts_with(strtolower($licence), 'cc0') => self::Cc0,
            str_starts_with(strtolower($licence), 'public domain'), strtolower($licence) == 'pd' => self::PublicDomain,
            default => null,
        };
    }
}
