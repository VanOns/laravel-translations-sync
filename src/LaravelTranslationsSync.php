<?php

namespace VanOns\LaravelTranslationsSync;

use Illuminate\Support\Facades\File;

class LaravelTranslationsSync
{
    /**
     * Check if a locale is allowed.
     */
    public function localeIsAllowed(string $key): bool
    {
        $keys = array_map('strtolower', config('translations-sync.locales'));

        return in_array(strtolower($key), $keys);
    }

    /**
     * Return all the translations.
     */
    public function getAllTranslations(): array
    {
        $strings = $this->getTranslationsForLocale(config('translations-sync.base_locale'));

        ksort($strings);

        return $strings;
    }

    /**
     * Return the translations for a specific locale.
     */
    public function getTranslationsForLocale(string $locale): array
    {
        $strings = [];

        // Load all translation files from the locale's directory.
        foreach (File::files(lang_path($locale)) as $file) {
            $name = basename($file);
            $strings[$name] = require $file;
            ksort($strings[$name]);
        }

        // Then also look for a JSON file with the same name as the locale.
        // From the locale (aa_BB), it first tries to find a JSON file with the BB part, then with the aa part.
        $jsonParts = explode('_', $locale);
        foreach (array_reverse($jsonParts) as $part) {
            $filename = strtolower($part) . '.json';
            $jsonPath = lang_path($filename);

            if (File::exists($jsonPath)) {
                $json = File::json($jsonPath);
                $strings[$filename] = $json;
                ksort($strings[$filename]);
                break;
            }
        }

        return $strings;
    }
}
