<?php

namespace VanOns\LaravelTranslationsSync;

use Illuminate\Support\Facades\File;

class LaravelTranslationsSync
{
    /**
     * Get the configured base locale.
     */
    public function getBaseLocale(): string
    {
        return config('translations-sync.base_locale');
    }

    /**
     * Get all configured locales.
     */
    public function getLocales(): array
    {
        $locales = config('translations-sync.locales', []);

        sort($locales);

        return $locales;
    }

    /**
     * Check if a locale is allowed.
     */
    public function localeIsAllowed(string $key): bool
    {
        return in_array(strtolower($key), array_map('strtolower', $this->getLocales()));
    }

    /**
     * Normalize the locale to a standard format.
     */
    public function normalizeLocale(string $locale): string
    {
        if (str_contains($locale, '_')) {
            $parts = explode('_', $locale);

            if (count($parts) >= 2) {
                return sprintf('%s_%s', strtolower($parts[0]), strtoupper($parts[1]));
            }
        }

        return strtolower($locale);
    }

    /**
     * Return all the translations.
     */
    public function getAllTranslations(): array
    {
        $strings = $this->getTranslationsForLocale($this->getBaseLocale());

        ksort($strings, SORT_STRING | SORT_FLAG_CASE);

        return $strings;
    }

    /**
     * Return the translations for a specific locale.
     */
    public function getTranslationsForLocale(string $locale): array
    {
        $normalizedLocale = $this->normalizeLocale($locale);
        $strings = [];

        // Load all translation files from the locale's directory.
        if (File::exists(lang_path($normalizedLocale))) {
            foreach (File::files(lang_path($normalizedLocale)) as $file) {
                $name = basename($file);
                $strings[$name] = require $file;
                ksort($strings[$name], SORT_STRING | SORT_FLAG_CASE);
            }
        }

        $jsonPath = lang_path("$normalizedLocale.json");
        if (File::exists($jsonPath)) {
            $json = File::get($jsonPath);
            $strings['json'] = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
            ksort($strings['json'], SORT_STRING | SORT_FLAG_CASE);
        }

        return $strings;
    }

    /**
     * Return the separator used in the translation keys.
     */
    public function getSeparator(): string
    {
        return config('translations-sync.separator', '.');
    }

    /**
     * Check if the cache is enabled.
     */
    public function cacheEnabled(): bool
    {
        return (bool) config('translations-sync.cache_enabled', true);
    }

    /**
     * Return the cache driver to use.
     */
    public function getCacheDriver(): string
    {
        return config('translations-sync.cache_driver', 'default');
    }
}
