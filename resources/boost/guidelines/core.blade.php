## Laravel Translations Sync (`van-ons/laravel-translations-sync`)

Syncs the app's translation files in `lang/` with a provider so that non-developers can edit translations there, and optionally fills missing translations through a translation API. One Artisan command, `lang:sync`, does everything. Built-in providers: Google Sheets for syncing (`google_sheets`) and DeepL for translating (`deepl`). The service provider and the `LaravelTranslationsSync` facade alias are auto-discovered.

### Setup

@verbatim
<code-snippet name="Install as a dev dependency" lang="bash">
composer require van-ons/laravel-translations-sync --dev
php artisan vendor:publish --tag=translations-sync-config
</code-snippet>

<code-snippet name=".env for Google Sheets and DeepL" lang="dotenv">
LTS_SYNC_PROVIDER=google_sheets
LTS_GOOGLE_SHEETS_AUTH_CONFIG="path/to/service-account.json"
LTS_GOOGLE_SHEETS_SPREADSHEET_ID=aabbccdd11223344
LTS_GOOGLE_SHEETS_SHEET_NAME=Sheet1
LTS_GOOGLE_SHEETS_FIRST_COLUMN=A
LTS_GOOGLE_SHEETS_LAST_COLUMN=C
LTS_GOOGLE_SHEETS_HEADING_ROW=1
LTS_GOOGLE_SHEETS_FIRST_ROW=2
LTS_GOOGLE_SHEETS_KEY_CELL_VALUE=KEY
LTS_GOOGLE_SHEETS_BASE_TRANSLATION_CELL_VALUE=EN

LTS_TRANSLATE_PROVIDER=deepl
LTS_DEEPL_API_KEY=...
LTS_DEEPL_API_URL=https://api-free.deepl.com   # or https://api.deepl.com
</code-snippet>
@endverbatim

- Set `base_locale` and `locales` in `config/translations-sync.php`; both default to `app.locale` only.
- The spreadsheet is shared with the Google service account's e-mail address (edit rights). Its heading row has the key column (`key_cell_value`), then a column per locale headed by the locale code (case-insensitive), the base locale's heading being `base_translation_cell_value`.

### Command

@verbatim
<code-snippet name="lang:sync" lang="bash">
php artisan lang:sync            # pull from the sheet, merge, write the sheet and lang/ files (asks to confirm)
php artisan lang:sync -R         # --retrieve-only: only write lang/, leave the sheet alone
php artisan lang:sync -T         # --translate: fill empty translations with the translate provider
php artisan lang:sync -R -F      # --force: skip the confirmation (needed when not interactive)
</code-snippet>
@endverbatim

### Config (`config/translations-sync.php`)

| Key | Default | Notes |
|---|---|---|
| `base_locale` | `config('app.locale')` | Its files decide which keys exist. |
| `locales` | `[config('app.locale')]` | Only these are written locally; other sheet columns are kept as they are. |
| `sync_provider` | `google_sheets` | Env `LTS_SYNC_PROVIDER`. The only provider. |
| `sync_providers.google_sheets.auth_config` | env | Path to the service account JSON. |
| `sync_providers.google_sheets.sheet.*` | env | `spreadsheet_id`, `sheet_name`, `first_column`, `last_column`, `heading_row`, `first_row`, `key_cell_value`, `base_translation_cell_value`. All required. |
| `translate_provider` | `deepl` | Env `LTS_TRANSLATE_PROVIDER`. The only provider. |
| `translate_providers.deepl.api_key` / `api_url` | env | `LTS_DEEPL_API_KEY`, `LTS_DEEPL_API_URL`. |
| `translate_providers.deepl.locale_map` | `[]` | App locale => DeepL target, e.g. `'de_CH' => 'de'`; `null` skips that locale. |
| `translate_wait_seconds` | `2` | Pause between DeepL requests. |
| `separator` | `'::'` | Joins file name and nested keys; must not occur in keys or values. |
| `cache_enabled` / `cache_driver` | `true` / `default` | Caches DeepL results per language forever; `file` stores them in `storage/app/translations/{locale}.json`. |
| `case_sensitive_sorting` | `false` | Env `LTS_CASE_SENSITIVE_SORTING`. |

### How a sync merges

- Keys come from the base locale only: every `lang/{base}/*.php` file (top level of that directory, not subdirectories or `lang/vendor`) and `lang/{base}.json`. In the sheet a key reads `auth.php::failed` or `json::Some text`.
- For a key that is both in the sheet and in the base files, **the sheet wins**, also for the base locale. Change an existing text in the sheet, not in the PHP file, or the next sync puts the sheet's text back.
- A row whose key no longer exists in the base locale files is dropped from the sheet. Empty cells for configured locales are filled from the local files.
- Without `-R` the command clears the sheet from `first_row` down and writes every row again.
- The `lang/` files of the configured locales are regenerated from the merged result and sorted by key: PHP files become plain nested arrays of strings (comments, closures and formatting are lost, empty nested arrays are left out) and JSON files are pretty-printed.
- `-T` sends only empty cells of configured locales to DeepL, translating from the base locale. `:placeholders` and `%s` are protected, and requests go in chunks of 50.

### Conventions and gotchas

- **Do not run `lang:sync` as a side effect.** It overwrites the shared sheet (unless `-R`) and the local translation files. Run it only when the user asks, and prefer `-R` to pull translations in. It asks to confirm, so pass `-F` when nothing can answer the prompt.
- Add new keys to the base locale files first; a key that only exists in another locale or in the sheet is dropped.
- Any other value for `sync_provider` or `translate_provider` throws `SyncException` / `TranslateException`; a provider with missing settings throws "not configured properly".
- `LaravelTranslationsSync` facade helpers: `getBaseLocale()`, `getLocales()`, `localeIsAllowed()`, `normalizeLocale()` (`nl_nl` → `nl_NL`), `getTranslationsForLocale($locale, flat: false)`, `getAllTranslations()`, `getSeparator()`. Global helpers `lts_array_dot()`, `lts_array_get()` and `lts_array_set()` are `Arr` equivalents with a custom separator.
