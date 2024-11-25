<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Locales
    |--------------------------------------------------------------------------
    |
    */

    'base_locale' => env('LTS_BASE_LOCALE', config('app.locale')),

    'locales' => [
        config('app.locale'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sync Providers
    |--------------------------------------------------------------------------
    |
    */

    'sync_provider' => env('LTS_SYNC_PROVIDER', 'google_sheets'),

    'sync_providers' => [
        'google_sheets' => [
            /**
             * Google Sheets API configuration
             */
            'auth_config' => env('LTS_GOOGLE_SHEETS_AUTH_CONFIG'),

            /**
             * Google Sheets API scopes
             */
            'scopes' => [
                Google_Service_Sheets::SPREADSHEETS,
            ],

            /**
             * Google Sheets API configurations
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

    'translate_provider' => env('LTS_TRANSLATE_PROVIDER', 'deepl'),

    'translate_providers' => [
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

];
