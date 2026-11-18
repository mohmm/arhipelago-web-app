<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait Translatable
{
    /**
     * Get the translatable attributes for the model.
     *
     * @return array<int, string>
     */
    public function getTranslatableAttributes(): array
    {
        return $this->translatable ?? ['name'];
    }

    /**
     * Get the translated value of an attribute for the current locale.
     */
    public function getTranslated(string $attribute): ?string
    {
        return $this->getTranslatedIn($attribute, app()->getLocale());
    }

    /**
     * Get the value of an attribute in the given language, falling back to the
     * content's main language when that translation is missing.
     */
    public function getTranslatedIn(string $attribute, string $locale): ?string
    {
        $fallback = config('translatable.fallback', 'ro');

        $value = $this->getAttribute("{$attribute}_{$locale}");

        if (empty($value) && $locale != $fallback) {
            $value = $this->getAttribute("{$attribute}_{$fallback}");
        }

        return $value;
    }

    /**
     * Get the translated name attribute.
     */
    protected function translatedName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getTranslated('name'),
        );
    }

    /**
     * Initialize the Translatable trait.
     */
    public function initializeTranslatable(): void
    {
        foreach ($this->getTranslatableAttributes() as $attribute) {
            $this->append("translated_{$attribute}");
        }
    }

    /**
     * Override getAttribute to support the translated_* prefix.
     *
     * @param  string  $key
     */
    public function getAttribute($key): mixed
    {
        if (str_starts_with($key, 'translated_')) {
            $attribute = str_replace('translated_', '', $key);

            if (in_array($attribute, $this->getTranslatableAttributes())) {
                return $this->getTranslated($attribute);
            }
        }

        return parent::getAttribute($key);
    }
}
