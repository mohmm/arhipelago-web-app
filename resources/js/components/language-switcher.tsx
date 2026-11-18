import { Globe } from 'lucide-react';
import { useState } from 'react';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

interface Language {
    code: string;
    name: string;
    native: string;
}

const supportedLanguages: Language[] = [
    { code: 'en', name: 'English', native: 'English' },
    { code: 'ro', name: 'Romanian', native: 'Română' },
];

const getCurrentLanguage = (locale: string): Language => {
    return supportedLanguages.find((lang) => lang.code === locale) || supportedLanguages[0];
};

interface LanguageSwitcherProps {
    className?: string;
}

export default function LanguageSwitcher({ className = '' }: LanguageSwitcherProps) {
    const [currentLocale, setCurrentLocale] = useState(() => {
        const htmlLang = document.documentElement.lang;

        return htmlLang || 'en';
    });

    const handleLanguageChange = (newLocale: string) => {
        setCurrentLocale(newLocale);
        const currentPath = window.location.pathname;
        const localePattern = new RegExp(`^/(${supportedLanguages.map((lang) => lang.code).join('|')})(?=/|$)`);
        const pathWithoutLocale = currentPath.replace(localePattern, '');
        const newPath = `/${newLocale}${pathWithoutLocale}`;
        window.location.href = newPath;
    };

    const currentLanguage = getCurrentLanguage(currentLocale);

    return (
        <div className={`flex items-center space-x-2 ${className}`}>
            <Globe className="h-4 w-4 text-muted-foreground" />
            <Select value={currentLocale} onValueChange={handleLanguageChange}>
                <SelectTrigger className="h-8 w-[120px] text-sm">
                    <SelectValue>{currentLanguage.native}</SelectValue>
                </SelectTrigger>
                <SelectContent>
                    {supportedLanguages.map((language) => (
                        <SelectItem key={language.code} value={language.code}>
                            <div className="flex items-center space-x-2">
                                <span className="text-sm font-medium">{language.native}</span>
                                <span className="text-xs text-muted-foreground">({language.name})</span>
                            </div>
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </div>
    );
}
