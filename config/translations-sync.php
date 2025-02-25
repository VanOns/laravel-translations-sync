<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base Locale
    |--------------------------------------------------------------------------
    |
    | The locale to use as base for all translations.
    |
    */

    'base_locale' => config('app.locale'),

    /*
    |--------------------------------------------------------------------------
    | Locales
    |--------------------------------------------------------------------------
    |
    | The locales that should be synced. If a locale is found, but it is not
    | in this list, it will be ignored.
    |
    */

    'locales' => [
        config('app.locale'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sync Provider
    |--------------------------------------------------------------------------
    |
    | The provider to synchronize translations with.
    |
    */

    'sync_provider' => env('LTS_SYNC_PROVIDER', 'google_sheets'),

    /*
    |--------------------------------------------------------------------------
    | Sync Providers
    |--------------------------------------------------------------------------
    |
    | The configurations for the available synchronization providers.
    |
    */

    'sync_providers' => [

        /**
         * Google Sheets configuration.
         */
        'google_sheets' => [

            /**
             * Path to the authentication configuration file.
             */
            'auth_config' => env('LTS_GOOGLE_SHEETS_AUTH_CONFIG'),

            /**
             * Scopes for the Google Sheets API.
             */
            'scopes' => [
                Google_Service_Sheets::SPREADSHEETS,
            ],

            /**
             * Configuration for the sheet.
             */
            'sheet' => [
                'spreadsheet_id' => env('LTS_GOOGLE_SHEETS_SPREADSHEET_ID'),
                'sheet_name' => env('LTS_GOOGLE_SHEETS_SHEET_NAME'),
                'first_column' => env('LTS_GOOGLE_SHEETS_FIRST_COLUMN'),
                'last_column' => env('LTS_GOOGLE_SHEETS_LAST_COLUMN'),
                'heading_row' => (int) env('LTS_GOOGLE_SHEETS_HEADING_ROW'),
                'first_row' => (int) env('LTS_GOOGLE_SHEETS_FIRST_ROW'),
                'key_cell_value' => env('LTS_GOOGLE_SHEETS_KEY_CELL_VALUE'),
                'base_translation_cell_value' => env('LTS_GOOGLE_SHEETS_BASE_TRANSLATION_CELL_VALUE'),
            ],

        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Translate Provider
    |--------------------------------------------------------------------------
    |
    | The provider to use for translations.
    |
    */

    'translate_provider' => env('LTS_TRANSLATE_PROVIDER', 'deepl'),

    /*
    |--------------------------------------------------------------------------
    | Translate Providers
    |--------------------------------------------------------------------------
    |
    | The configurations for the available translation providers.
    |
    */

    'translate_providers' => [

        /**
         * DeepL configuration.
         */
        'deepl' => [

            'api_key' => env('LTS_DEEPL_API_KEY'),
            'api_url' => env('LTS_DEEPL_API_URL'),

        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Translate Wait Seconds
    |--------------------------------------------------------------------------
    |
    | The amount of seconds to wait between each translation request.
    | Set to 0 to disable waiting.
    |
    */

    'translate_wait_seconds' => env('LTS_TRANSLATE_WAIT_SECONDS', 2),

    /*
    |--------------------------------------------------------------------------
    | Separator
    |--------------------------------------------------------------------------
    |
    | The separator to use for splitting keys and nested values. Set this to a
    | value that is not used in your translation keys or values.
    |
    */

    'separator' => '::',

    /*
    |--------------------------------------------------------------------------
    | Cache Enabled
    |--------------------------------------------------------------------------
    |
    | Enabling the cache will store the translations in the cache for a certain
    | amount of time. This will reduce the amount of requests to the translation
    | provider.
    |
    */

    'cache_enabled' => env('LTS_CACHE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Cache Driver
    |--------------------------------------------------------------------------
    |
    | The cache driver to use for storing the translations.
    |
    | Supported drivers:
    | - "default" (uses Laravel's default cache driver)
    | - "file"
    |
    */

    'cache_driver' => env('LTS_CACHE_DRIVER', 'default'),

];
