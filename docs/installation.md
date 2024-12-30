# Installation

Install the package via Composer as dev dependency:

```bash
composer require van-ons/laravel-translations-sync --dev
```

Then, publish the configuration file:

```bash
php artisan vendor:publish --tag="translations-sync-config"
```

## Configuration

First it is important to set `base_locale` and `locales` in the configuration file to match your project's configuration.
`base_locale` is the locale that will be used as the source for translations. By default, it is set to your app locale.
`locales` is an array of locales that will be synced. By default, it only contains the app locale.

Next you need to set up the sync provider. The following providers are supported:

- [Google Sheets](sync/google-sheets.md)

If you also want to be able to translate missing translations, you need to set up a translation provider. This is not
required to use the package. The following providers are supported:

- [DeepL](translate/deepl.md)
