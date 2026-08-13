# Upgrading

We aim to make upgrading between versions as smooth as possible, but sometimes it involves specific steps to be taken.
This document will outline those steps. And as much as we try to cover all cases, we might miss some. If you come
across such a case, please let us know by [opening an issue][issues], or by adding it yourself and creating a pull request.

<!-- EXAMPLE -->
<!--
# v1 to v2

* Remove the `foo` column from the `bar` table.
* Add the `baz` column to the `bar` table.
* Run `php artisan migrate` to update the database.
-->

# v0.5.1 to v0.6.0

* A new `case_sensitive_sorting` config option (env: `LTS_CASE_SENSITIVE_SORTING`) controls whether translation keys
  are sorted case-sensitively or case-insensitively. It defaults to `false` (case-insensitive), so no action is
  required unless you want case-sensitive sorting.

[issues]: https://github.com/VanOns/laravel-translations-sync/issues