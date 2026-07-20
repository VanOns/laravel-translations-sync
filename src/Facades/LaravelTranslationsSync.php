<?php

namespace VanOns\LaravelTranslationsSync\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \VanOns\LaravelTranslationsSync\LaravelTranslationsSync
 */
class LaravelTranslationsSync extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-translations-sync';
    }
}
