<p align="center"><img src="art/social-card.png" alt="Social card of Laravel Translations Sync"></p>

# Laravel Translations Sync

[![Latest Version on Github](https://img.shields.io/github/release/VanOns/laravel-translations-sync.svg?style=flat-square)](https://github.com/VanOns/laravel-translations-sync/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/van-ons/laravel-translations-sync.svg?style=flat-square)](https://packagist.org/packages/van-ons/laravel-translations-sync)
[![Github issues](https://img.shields.io/github/issues/VanOns/laravel-translations-sync?style=flat-square)](https://github.com/VanOns/laravel-translations-sync/issues)
[![License](https://img.shields.io/github/license/VanOns/laravel-translations-sync?style=flat-square)](https://github.com/VanOns/laravel-translations-sync/blob/main/LICENSE.md)
[![Plumb score](https://img.shields.io/badge/dynamic/regex?url=https%3A%2F%2Fplumbphp.dev%2Fbadges%2Fvan-ons%2Flaravel-translations-sync%2Fcomposite.svg&search=%3Ctitle%3Eplumb%3A%5Cs%2A%28%5B%5E%3C%5D%2B%29%3C&replace=%241&label=plumb&style=flat-square)](https://plumbphp.dev/van-ons/laravel-translations-sync)

A package that synchronizes translations between your Laravel project and a provider.

## Quick start

### Requirements

| Dependency | Minimum version |
|------------|-----------------|
| PHP        | 8.0             |
| Laravel    | 9.0             |

### Installation

First, install the package via Composer as dev dependency:

```bash
composer require van-ons/laravel-translations-sync --dev
```

Then, publish the configuration file:

```bash
php artisan vendor:publish --tag="translations-sync-config"
```

Next, follow the [configuration steps](docs/installation.md#configuration) to set up the configuration file and providers.

### Usage

You can execute the synchronization command by running:

```bash
php artisan lang:sync
````

See [Basic usage](docs/basic-usage.md) for more information.

## Documentation

Please see the [documentation](docs/README.md) for detailed information about installation and usage.

## Contributing

Please see [Contributing](CONTRIBUTING.md) for more information about how you can contribute.

## Changelog

Please see [Changelog](CHANGELOG.md) for more information about what has changed recently.

## Upgrading

Please see [Upgrading](UPGRADING.md) for more information about how to upgrade.

## Security

Please see [Security](SECURITY.md) for more information about how we deal with security.

## Credits

We would like to thank the following contributors for their contributions to this project:

- [All contributors](../../contributors)

## License

The scripts and documentation in this project are released under the [MIT License](LICENSE.md).

---

<p align="center"><a href="https://van-ons.nl/" target="_blank"><img src="https://opensource.van-ons.nl/files/cow.png" width="50" alt="Logo of Van Ons"></a></p>
