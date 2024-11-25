<?php

namespace VanOns\LaravelTranslationsSync\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array getAllTranslations()
 * @method static array getTranslationsForLocale(string $locale)
 *
 * @mixin \VanOns\LaravelTranslationsSync\LaravelTranslationsSync
 */
class LaravelTranslationsSync extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-translations-sync';
    }
}
