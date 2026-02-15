---
title: Base installation
weight: 2
---

Install the package through Composer:

```bash
composer require hosmelq/laravel-model-preferences
```

Run the package installer for interactive setup:

```bash
php artisan model-preferences:install
```

The installer can publish:

- run migrations
- publish `config/model-preferences.php`
- publish the shared preferences migration

## Publishing the config file

Publishing the config file is optional:

```bash
php artisan vendor:publish --provider="HosmelQ\ModelPreferences\ModelPreferencesServiceProvider" --tag="laravel-model-preferences-config"
```

## Preparing the database

Publish the migration to create the shared preferences table:

```bash
php artisan vendor:publish --provider="HosmelQ\ModelPreferences\ModelPreferencesServiceProvider" --tag="laravel-model-preferences-migrations"
php artisan migrate
```

If you prefer manual publishing, run both commands and then migrate.

This is the default content of the config file:

```php
return [
    'default' => env('MODEL_PREFERENCES_DRIVER', 'shared'),

    'stores' => [
        'column' => [
            'driver' => 'column',
            'name' => env('MODEL_PREFERENCES_COLUMN_NAME', 'preferences'),
        ],

        'table' => [
            'connection' => env('MODEL_PREFERENCES_DB_CONNECTION', env('DB_CONNECTION')),
            'driver' => 'table',
        ],

        'shared' => [
            'connection' => env('MODEL_PREFERENCES_DB_CONNECTION', env('DB_CONNECTION')),
            'driver' => 'shared',
            'table' => env('MODEL_PREFERENCES_TABLE', 'preferences'),
        ],
    ],
];
```

## Environment variables

- `MODEL_PREFERENCES_DRIVER`: `column`, `table`, or `shared` (defaults to `shared`)
- `MODEL_PREFERENCES_COLUMN_NAME`: preferred JSON column for the column driver
- `MODEL_PREFERENCES_DB_CONNECTION`: connection for shared/table tables
- `MODEL_PREFERENCES_TABLE`: default table name for shared driver

## Per-model overrides

Use `PreferencesConfig::configure()` in your model to override defaults:

```php
return PreferencesConfig::configure()
    ->withDriver('column')
    ->withColumn('settings')
    ->withDefaults([
        'language' => 'en',
    ])
    ->withRules([
        'language' => ['required', 'string', 'in:en,es,fr'],
    ])
    ->withTable('users_preferences');
```

## Create a table migration for table drivers

Use the package command to scaffold a custom preferences table:

```bash
php artisan model-preferences:table name_of_the_preferences_table
```

This command creates a migration file with `id`, owner key, `key`, `value`, and timestamps.

If needed, inspect `stubs/table-preferences.stub` and adjust it for your schema.
