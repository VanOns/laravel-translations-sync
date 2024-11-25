<?php

namespace VanOns\LaravelTranslationsSync\Services\Translate;

use Illuminate\Support\Collection;
use VanOns\LaravelTranslationsSync\Facades\LaravelTranslationsSync;
use VanOns\LaravelTranslationsSync\Services\Traits\HasCommand;

abstract class BaseTranslateService
{
    use HasCommand;

    protected static string $name = '';

    abstract public function setUp(): static;

    abstract public function isEnabled(): bool;

    public function getName(): string
    {
        return static::$name;
    }

    abstract protected function translate(string $text, string $targetLanguage): ?string;

    public function translateAll(Collection $translations, Collection $translationKeys, string $baseTranslationKey, string $baseKey): Collection
    {
        if (!$this->isEnabled()) {
            return $translations;
        }

        $this->info('Starting translating...');

        $progress = $this->command->getOutput()->createProgressBar(count($translations));
        $progress->start();

        $translateCache = [];

        foreach ($translations as $translationIndex => $values) {
            $baseKeyIndex = $translationKeys->search($baseKey);

            // Set the base translation, which will be needed to translate the other languages.
            $baseLanguageIndex = $translationKeys->search($baseTranslationKey);
            $baseTranslation = $values[$baseLanguageIndex] ?? null;

            if (empty($baseTranslation)) {
                continue;
            }

            $preparedBaseTranslation = $this->beforeTranslating($baseTranslation);

            foreach ($values as $valueIndex => $value) {
                // Don't translate the translation key.
                if ($valueIndex === $baseKeyIndex) {
                    continue;
                }

                $language = $translationKeys[$valueIndex] ?? null;

                // Preserve the value if the language is empty, or not allowed.
                if (empty($language) || !LaravelTranslationsSync::localeIsAllowed($language)) {
                    continue;
                }

                // Translate if we have a language and no value.
                if (empty($value)) {
                    $translated = $translateCache[$language][$baseTranslation] ?? null;

                    if (empty($translated)) {
                        $translated = $this->translate($preparedBaseTranslation, $language);
                        $translated = $this->afterTranslating($translated, $baseTranslation);

                        if (!isset($translateCache[$language])) {
                            $translateCache[$language] = [];
                        }

                        $translateCache[$language][$baseTranslation] = $translated;
                    }

                    $translations[$translationIndex][$valueIndex] = $translated;
                }
            }

            $progress->advance();
        }

        $progress->finish();

        $this->info('Translating completed.');

        return $translations;
    }

    public function beforeTranslating(string $value): string
    {
        return $value;
    }

    public function afterTranslating(string $value, string $original): string
    {
        return $value;
    }
}
