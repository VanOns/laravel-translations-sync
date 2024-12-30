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
        $locales = array_map('strtolower', config('translations-sync.locales', []));

        sort($locales);

        return $locales;
    }

    /**
     * Check if a locale is allowed.
     */
    public function localeIsAllowed(string $key): bool
    {
        return in_array(strtolower($key), $this->getLocales());
    }

    /**
     * Return all the translations.
     */
    public function getAllTranslations(): array
    {
        $strings = $this->getTranslationsForLocale($this->getBaseLocale());

        ksort($strings, SORT_NATURAL | SORT_FLAG_CASE);

        return $strings;
    }

    /**
     * Return the translations for a specific locale.
     */
    public function getTranslationsForLocale(string $locale): array
    {
        $strings = [];

        // Load all translation files from the locale's directory.
        if (File::exists(lang_path($locale))) {
            foreach (File::files(lang_path($locale)) as $file) {
                $name = basename($file);
                $strings[$name] = require $file;
                ksort($strings[$name], SORT_NATURAL | SORT_FLAG_CASE);
            }
        }

        // Then also look for a JSON file with the same name as the locale.
        // From the locale (aa_BB), it first tries to find a JSON file with the BB part, then with the aa part.
        $jsonParts = explode('_', $locale);
        foreach ([$locale, ...array_reverse($jsonParts)] as $part) {
            $filename = strtolower($part) . '.json';
            $jsonPath = lang_path($filename);

            if (File::exists($jsonPath)) {
                $json = File::get($jsonPath);
                $strings['json'] = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
                ksort($strings['json'], SORT_NATURAL | SORT_FLAG_CASE);
                break;
            }
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
