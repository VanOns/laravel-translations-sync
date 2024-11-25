# Package skeleton

This package is meant to give you a default setup for building a new
laravel package.

What's included:

- [PHP-CS-Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer) setup
- [PHPSTAN](https://phpstan.org/) setup
- Default service provider and layout to get started with.

## Usage

- Clone this package
- Review `composer.json`:
  - Change the name property, description, license.
  - Change the namespace and service provider classname in the `autoload` and
  `extra` properties.
- Remove from the `src/SkeletonServiceProvider` what you won't be using.
- Change the namespace in the `src` directory's files.

## Development

To develop your package inside an existing Laravel project, add the location
of the package in a `repositories` property to the `composer.json` file of the
parent project. If your package would be inside a `packages` directory inside
the parent project:

```json
"repositories": [
    {
      "type": "path",
      "url": "packages/skeleton"
    }
  ]
```

Then require the package: `composer require van-ons/skeleton`.

Laravel >8 will throw a version constraint error. You can fix this by doing
one of the following:

1. Change the `minimum-stability` property to `dev` in your `composer.json`
file.
2. add a version property to your package's `composer.json` file:

```json
{
  "version": "1.0.0",
}
```

## Documentation and alternatives

- [Laravel documentation](https://laravel.com/docs/11.x/packages)
- [LaravelPackage.com](https://www.laravelpackage.com/)
- [Spatie's package skeleton](https://github.com/spatie/package-skeleton-laravel/)
