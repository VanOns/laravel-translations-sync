<?php

use Illuminate\Support\Facades\File;
use VanOns\LaravelTranslationsSync\Facades\LaravelTranslationsSync;

it('returns configured base locale', function () {
    expect(LaravelTranslationsSync::getBaseLocale())->toBe('en');
});

it('returns sorted locales', function () {
    expect(LaravelTranslationsSync::getLocales())->toBe(['de', 'en', 'nl']);
});

it('allows a locale that is in the configured list', function () {
    expect(LaravelTranslationsSync::localeIsAllowed('en'))->toBeTrue();
    expect(LaravelTranslationsSync::localeIsAllowed('nl'))->toBeTrue();
});

it('rejects a locale that is not in the configured list', function () {
    expect(LaravelTranslationsSync::localeIsAllowed('fr'))->toBeFalse();
});

it('checks locale allowance case-insensitively', function () {
    expect(LaravelTranslationsSync::localeIsAllowed('EN'))->toBeTrue();
    expect(LaravelTranslationsSync::localeIsAllowed('NL'))->toBeTrue();
});

it('normalizes a simple locale to lowercase', function () {
    expect(LaravelTranslationsSync::normalizeLocale('en'))->toBe('en');
    expect(LaravelTranslationsSync::normalizeLocale('EN'))->toBe('en');
});

it('normalizes an underscore locale to lower_UPPER format', function () {
    expect(LaravelTranslationsSync::normalizeLocale('de_CH'))->toBe('de_CH');
    expect(LaravelTranslationsSync::normalizeLocale('de_ch'))->toBe('de_CH');
    expect(LaravelTranslationsSync::normalizeLocale('DE_CH'))->toBe('de_CH');
});

it('returns configured separator', function () {
    expect(LaravelTranslationsSync::getSeparator())->toBe('::');
});

it('returns cache enabled state', function () {
    expect(LaravelTranslationsSync::cacheEnabled())->toBeTrue();
});

it('returns configured cache driver', function () {
    expect(LaravelTranslationsSync::getCacheDriver())->toBe('default');
});

it('returns translations for a locale from php files', function () {
    $langPath = lang_path('en');
    File::ensureDirectoryExists($langPath);
    File::put("{$langPath}/messages.php", '<?php return ["hello" => "Hello", "world" => "World"];');

    $translations = LaravelTranslationsSync::getTranslationsForLocale('en');

    expect($translations)->toHaveKey('messages.php');
    expect($translations['messages.php'])->toBe(['hello' => 'Hello', 'world' => 'World']);

    File::deleteDirectory($langPath);
});

it('returns translations from json file', function () {
    $jsonPath = lang_path('en.json');
    File::put($jsonPath, json_encode(['Hello' => 'Hello', 'Goodbye' => 'Goodbye']));

    $translations = LaravelTranslationsSync::getTranslationsForLocale('en');

    expect($translations)->toHaveKey('json');
    expect($translations['json'])->toBe(['Goodbye' => 'Goodbye', 'Hello' => 'Hello']);

    File::delete($jsonPath);
});

it('returns empty array when no translation files exist for locale', function () {
    expect(LaravelTranslationsSync::getTranslationsForLocale('xx'))->toBe([]);
});

it('getAllTranslations returns base locale translations sorted by key', function () {
    $langPath = lang_path('en');
    File::ensureDirectoryExists($langPath);
    File::put("{$langPath}/auth.php", '<?php return ["failed" => "Failed", "password" => "Wrong password"];');

    $translations = LaravelTranslationsSync::getAllTranslations();

    expect($translations)->toHaveKey('auth.php');
    expect(array_keys($translations))->toBe(array_keys($translations));

    File::deleteDirectory($langPath);
});
