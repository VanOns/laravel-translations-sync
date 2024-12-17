<p align="center"><img src="art/social-card.png" alt="Social card of Laravel Translations Sync"></p>

# Laravel Translations Sync

<!-- BADGES -->

A Laravel package that synchronizes translations between local and remote.

## Quick start

### Installation

Install the package via Composer:

```bash
composer require van-ons/laravel-translations-sync
```

Then, publish the configuration file:

```bash
php artisan vendor:publish --provider="VanOns\LaravelTranslationsSync\LaravelTranslationsSyncServiceProvider" --tag="translations-sync-config"
```

Next, set `base_locale` and `locales` in the configuration file to match your project's configuration. The other settings
can be configured using environment variables.

### Usage

You can execute the synchronization command by running:

```bash
php artisan lang:sync
````

> [!NOTE]
> Before any destructive action is taken, you will be asked to confirm the action.

The command supports the following flags:

| Flag                    | Description                                                   |
|-------------------------|---------------------------------------------------------------|
| `-R`, `--retrieve-only` | Only write the translations locally, don't update the remote  |
| `-T`, `--translate`     | Translate missing translations using the translation provider |
| `-F`, `--force`         | Skip the confirmation dialog                                  |

## Documentation

Please see the [documentation] for detailed information about installation and usage.

## Contributing

Please see [contributing] for more information about how you can contribute.

## Changelog

Please see [changelog] for more information about what has changed recently.

## Upgrading

Please see [upgrading] for more information about how to upgrade.

## Security

Please see [security] for more information about how we deal with security.

## Credits

We would like to thank the following contributors for their contributions to this project:

- [All Contributors][all-contributors]

## License

The scripts and documentation in this project are released under the [GPL-3.0 License][license].

---

<p align="center"><a href="https://van-ons.nl/" target="_blank"><img src="https://opensource.van-ons.nl/files/cow.png" width="50" alt="Logo of Van Ons"></a></p>

[documentation]: docs
[contributing]: CONTRIBUTING.md
[changelog]: CHANGELOG.md
[upgrading]: UPGRADING.md
[security]: SECURITY.md
[email]: mailto:opensource@van-ons.nl
[all-contributors]: ../../contributors
[license]: LICENSE.md
