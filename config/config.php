<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Locales
    |--------------------------------------------------------------------------
    |
    */

    /**
     * The locale to use as base for all translations.
     */
    'base_locale' => config('app.locale'),

    /**
     * The locales that should be synced. If a locale is found, but it is not
     * in this list, it will be ignored.
     */
    'locales' => [
        config('app.locale'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sync Providers
    |--------------------------------------------------------------------------
    |
    */

    /**
     * The provider to synchronize translations with.
     */
    'sync_provider' => env('LTS_SYNC_PROVIDER', 'google_sheets'),

    /**
     * The configurations for the available synchronization providers.
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
    | Translate Providers
    |--------------------------------------------------------------------------
    |
    */

    /**
     * The provider to use for translations.
     */
    'translate_provider' => env('LTS_TRANSLATE_PROVIDER', 'deepl'),

    /**
     * The configurations for the available translation providers.
     */
    'translate_providers' => [

        /**
         * Deepl configuration.
         */
        'deepl' => [

            /**
             * Deepl API key
             */
            'api_key' => env('LTS_DEEPL_API_KEY'),

            /**
             * Deepl API url
             */
            'api_url' => env('LTS_DEEPL_API_URL'),

        ],

    ],

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

];
