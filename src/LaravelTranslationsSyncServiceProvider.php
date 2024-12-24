<?php

namespace VanOns\LaravelTranslationsSync;

use Illuminate\Support\ServiceProvider;
use VanOns\LaravelTranslationsSync\Console\SyncTranslations;

class LaravelTranslationsSyncServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes(
            paths: [
                __DIR__ . '/../config/config.php' => config_path('translations-sync.php'),
            ],
            groups: 'translations-sync-config'
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncTranslations::class,
            ]);
        }
    }

    public function register()
    {
        $this->app->bind('laravel-translations-sync', function () {
            return new LaravelTranslationsSync();
        });

        $this->mergeConfigFrom(
            __DIR__ . '/../config/config.php',
            'translations-sync'
        );
    }
}
