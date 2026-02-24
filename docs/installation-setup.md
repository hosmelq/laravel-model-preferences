---
title: Installation & setup
weight: 2
---

You can install the package via Composer:

```bash
composer require hosmelq/laravel-model-preferences
```

## Run the installer (recommended)

After installing, run the package installer:

```bash
php artisan model-preferences:install
```

The installer can:

- run migrations
- publish `config/model-preferences.php`
- publish the shared preferences migration

If you already completed these steps in the installer, you can skip the manual sections below.

## Publish the config file (optional)

If you prefer to publish the config manually:

```bash
php artisan vendor:publish --provider="HosmelQ\ModelPreferences\ModelPreferencesServiceProvider" --tag="laravel-model-preferences-config"
```

## Set up the database

If you use the default `shared` driver, publish and run the package migration:

```bash
php artisan vendor:publish --provider="HosmelQ\ModelPreferences\ModelPreferencesServiceProvider" --tag="laravel-model-preferences-migrations"
php artisan migrate
```

If you use the `column` driver, no additional table is required.

If you use the `table` driver, generate a dedicated preferences table migration with `php artisan model-preferences:table name_of_the_preferences_table`.

