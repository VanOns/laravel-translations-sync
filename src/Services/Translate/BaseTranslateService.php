<?php

namespace VanOns\LaravelTranslationsSync\Services\Translate;

use Illuminate\Support\Collection;
use VanOns\LaravelTranslationsSync\Facades\LaravelTranslationsSync;
use VanOns\LaravelTranslationsSync\Services\Traits\HasCommand;

abstract class BaseTranslateService
{
    use HasCommand;

    protected static string $name = '';

    protected int $waitSeconds = 2;

    public function __construct()
    {
        $this->waitSeconds = config('translations-sync.translate_wait_seconds', 2);
    }

    abstract public function setUp(): static;

    abstract public function isEnabled(): bool;

    public function getName(): string
    {
        return static::$name;
    }

    public function translateAll(Collection $translations, Collection $translationKeys, string $baseTranslationKey, string $baseKey): Collection
    {
        if (!$this->isEnabled()) {
            return $translations;
        }

        $translatable = $this->buildTranslatable($translations, $translationKeys, $baseTranslationKey, $baseKey);

        return $this->processTranslatable($translations, $translatable);
    }

    /**
     * Build the translatable array.
     */
    protected function buildTranslatable(Collection $translations, Collection $translationKeys, string $baseTranslationKey, string $baseKey): Collection
    {
        return $translations;
    }

    /**
     * Process the translatable array.
     */
    protected function processTranslatable(Collection $translations, Collection $translatable): Collection
    {
        return $translations;
    }

    /**
     * Translate the text.
     */
    abstract protected function translate(string|array $text, string $targetLanguage): string|array|null;

    /**
     * Process the value before translating.
     */
    public function beforeTranslating(string $value): string
    {
        return $value;
    }

    /**
     * Process the value after translating.
     */
    public function afterTranslating(string $value, string $original): string
    {
        return $value;
    }
}
