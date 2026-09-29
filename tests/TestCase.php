<?php

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use VanOns\LaravelTranslationsSync\LaravelTranslationsSyncServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelTranslationsSyncServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('translations-sync.base_locale', 'en');
        $app['config']->set('translations-sync.locales', ['en', 'nl', 'de']);
        $app['config']->set('translations-sync.separator', '::');
        $app['config']->set('translations-sync.cache_enabled', true);
        $app['config']->set('translations-sync.cache_driver', 'default');
    }
}
