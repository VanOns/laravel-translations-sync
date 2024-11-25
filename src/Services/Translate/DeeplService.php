<?php

namespace VanOns\LaravelTranslationsSync\Services\Translate;

use Exception;
use Illuminate\Support\Facades\Http;
use VanOns\LaravelTranslationsSync\Exceptions\TranslateException;

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
    public function translate(string $text, string $targetLanguage): ?string
    {
        if (!$this->isEnabled()) {
            return null;
        }

        try {
            $response = Http::withHeaders(['Authorization' => 'DeepL-Auth-Key ' . $this->apiKey, 'Accept' => 'application/json'])
                ->post($this->apiUrl . '/v2/translate', [
                    'text' => [$text],
                    'target_lang' => $targetLanguage,
                ])->throw();

            if (!$response->successful()) {
                throw TranslateException::translateFailed($response->body());
            }

            $translations = $response->json('translations');

            // Check if translations are present
            if (!empty($translations) && isset($translations[0]['text'])) {
                return $translations[0]['text'];
            }

            return null;
        } catch (Exception $e) {
            if (is_a($e, TranslateException::class)) {
                throw $e;
            }

            throw TranslateException::translateFailed($e->getMessage());
        }
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
