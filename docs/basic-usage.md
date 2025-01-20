# Basic usage

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
