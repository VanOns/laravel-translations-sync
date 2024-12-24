<?php

namespace VanOns\LaravelTranslationsSync\Services\Translate;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use VanOns\LaravelTranslationsSync\Exceptions\TranslateException;
use VanOns\LaravelTranslationsSync\Facades\LaravelTranslationsSync;

class DeeplService extends BaseTranslateService
{
    protected static string $name = 'DeepL';

    protected static string $valueParseRegex = '/(:\w+|%s)/';

    protected ?string $apiKey = null;

    protected ?string $apiUrl = null;

    /**
     * @throws TranslateException
     */
    public function setUp(): static
    {
        $this->apiKey = config('translations-sync.translate_providers.deepl.api_key');
        $this->apiUrl = config('translations-sync.translate_providers.deepl.api_url');

        if (!$this->isEnabled()) {
            throw TranslateException::providerNotConfigured();
        }

        return $this;
    }

    public function isEnabled(): bool
    {
        return !empty($this->apiKey) && !empty($this->apiUrl);
    }

    /**
     * @throws TranslateException
     */
    public function translate(string|array $text, string $targetLanguage): string|array|null
    {
        if (!$this->isEnabled()) {
            return null;
        }

        try {
            $response = Http::withHeaders(['Authorization' => 'DeepL-Auth-Key ' . $this->apiKey, 'Accept' => 'application/json'])
                ->post($this->apiUrl . '/v2/translate', [
                    'text' => is_array($text) ? array_values($text) : [$text],
                    'target_lang' => $targetLanguage,
                ])->throw();

            if (!$response->successful()) {
                throw TranslateException::translateFailed($response->body());
            }

            $translations = $response->json('translations');

            // Return null if the translations are empty or the text key is not set.
            if (empty($translations) || !isset($translations[0]['text'])) {
                return null;
            }

            // Return the translations as an array if the input was an array.
            if (is_array($text)) {
                return array_column($translations, 'text');
            }

            return $translations[0]['text'];
        } catch (Exception $e) {
            if (is_a($e, TranslateException::class)) {
                throw $e;
            }

            throw TranslateException::translateFailed($e->getMessage());
        }
    }

    /**
     * Build the translatable array.
     */
    protected function buildTranslatable(Collection $translations, Collection $translationKeys, string $baseTranslationKey, string $baseKey): Collection
    {
        $this->info('Starting translating...');

        $progress = $this->command->getOutput()->createProgressBar(count($translations));
        $progress->start();

        $translatable = collect();

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
                    $translatable[$language][$baseTranslation] = [
                        'translation_index' => $translationIndex,
                        'value_index' => $valueIndex,
                        'base_translation' => $baseTranslation,
                        'prepared_translation' => $preparedBaseTranslation,
                    ];
                }
            }

            $progress->advance();
        }

        $progress->finish();

        return $translatable;
    }

    /**
     * Process the translatable array.
     */
    protected function processTranslatable(Collection $translations, Collection $translatable): Collection
    {
        $this->info('Sending translations to provider...');

        $translateCache = [];

        foreach ($translatable as $language => $translatables) {
            $this->info("Translating translations for $language...");

            $translateCache[$language] = array_merge(
                $this->loadCache($language),
                $translateCache[$language] ?? []
            );

            // Process the translations in chunks of 50, which is DeepL's limit.
            foreach (array_chunk($translatables, 50) as $chunk) {
                foreach ($chunk as $i => $translation) {
                    $baseTranslation = $translation['base_translation'];
                    $translated = $translateCache[$language][$baseTranslation] ?? null;

                    if (!empty($translated)) {
                        $translations[$translation['translation_index']][$translation['value_index']] = $translated;
                        unset($chunk[$i]);
                    }
                }

                // An empty chunk means all translations are already cached, in which case we can just continue.
                if (empty($chunk)) {
                    continue;
                }

                $translated = $this->translate(array_column($chunk, 'prepared_translation'), $language);

                // Process the translations, cache them, and set them on the translations array.
                foreach ($translated as $i => $result) {
                    $currentTranslation = $chunk[$i];
                    $baseTranslation = $currentTranslation['base_translation'];
                    $processedTranslation = $this->afterTranslating($result, $baseTranslation);

                    if (!isset($translateCache[$language])) {
                        $translateCache[$language] = [];
                    }

                    $translateCache[$language][$baseTranslation] = $processedTranslation;
                    $translations[$currentTranslation['translation_index']][$currentTranslation['value_index']] = $processedTranslation;
                }

                // Sleep for 2 seconds to prevent rate limiting.
                sleep(2);
            }

            $this->saveCache($language, $translateCache);
        }

        $this->info('Translating completed.');

        return $translations;
    }

    /**
     * Load the cache from a JSON file.
     */
    protected function loadCache(string $language): array
    {
        // Make sure the cache path exists.
        File::ensureDirectoryExists(storage_path('app/translations'));

        // Load the current translations from the cache.
        if (File::exists(storage_path("app/translations/{$language}.json"))) {
            $currentFile = File::get(storage_path("app/translations/{$language}.json"));

            return json_decode($currentFile, true, flags: JSON_THROW_ON_ERROR);
        }

        return [];
    }

    /**
     * Save the cache to a JSON file.
     */
    protected function saveCache(string $language, array $translateCache = []): void
    {
        $newJson = $translateCache[$language];

        ksort($newJson, SORT_NATURAL | SORT_FLAG_CASE);

        File::put(storage_path("app/translations/{$language}.json"), json_encode($newJson, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
    }

    /**
     * Replace all placeholders (:name, %s, etc.) with an XML tag containing a unique ID.
     * This way we can replace the placeholders back after the translation.
     *
     * Thanks to DeepL's own example: https://github.com/DeepLcom/deepl-python/tree/main/examples/mustache
     */
    public function beforeTranslating(string $value): string
    {
        $count = 0;

        return preg_replace_callback(static::$valueParseRegex, static function () use (&$count) {
            $replacement = '<m id="' . $count . '" />';
            $count++;
            return $replacement;
        }, $value);
    }

    /**
     * Replace the placeholders back with the original value.
     */
    public function afterTranslating(string $value, string $original): string
    {
        preg_match_all(static::$valueParseRegex, $original, $originalMatches);

        return preg_replace_callback('/<m id="(\d+)" \/>/', static function ($matches) use ($originalMatches) {
            $index = (int) $matches[1];
            return $originalMatches[1][$index];
        }, $value);
    }
}
