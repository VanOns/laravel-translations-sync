# Usage

You can execute the synchronization command by running:

```bash
php artisan lang:sync
```

> [!NOTE]
> Before any destructive action is taken, you will be asked to confirm the action (except when using the `force` flag).

The command supports the following flags:

| Flag                    | Description                                                     |
|-------------------------|-----------------------------------------------------------------|
| `-R`, `--retrieve-only` | Only write the translations locally, do not update the provider |
| `-T`, `--translate`     | Translate missing translations using the translation provider   |
| `-F`, `--force`         | Skip the confirmation dialog                                    |

## Sorting

Translation keys are sorted alphabetically before being written. By default, this sorting is case-insensitive.
Set `case_sensitive_sorting` (or the `LTS_CASE_SENSITIVE_SORTING` env variable) to `true` in the configuration file
to sort keys case-sensitively instead (uppercase before lowercase).
