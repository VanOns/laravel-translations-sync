<?php

namespace VanOns\LaravelTranslationsSync\Services\Sync;

use Illuminate\Support\Collection;
use VanOns\LaravelTranslationsSync\Services\Traits\HasCommand;

abstract class BaseSyncService
{
    use HasCommand;

    protected static string $name = '';

    protected Collection $translations;

    public function __construct()
    {
        $this->translations = collect();
    }

    abstract public function setUp(): static;

    abstract public function isEnabled(): bool;

    public function getName(): string
    {
        return static::$name;
    }

    /**
     * Retrieve all the existing translations.
     */
    abstract public function getTranslations(): Collection;

    public function mapSourceTranslation(mixed $translation, string|int $key): array
    {
        return [$key => $translation];
    }

    abstract public function getTranslationKeys(): Collection;

    abstract public function getBaseTranslationKey(): string;

    abstract public function getBaseKey(): string;

    abstract public function filterTargetTranslation(array $translation, Collection $localTranslations): bool;

    public function parseAllTranslations(Collection $translations, Collection $localTranslations): Collection
    {
        return $translations;
    }

    public function mapTranslationForWrite(Collection $translation): array
    {
        return $translation->toArray();
    }

    /**
     * Write the new translations to the provider.
     */
    abstract public function write(Collection $translations): void;
}
