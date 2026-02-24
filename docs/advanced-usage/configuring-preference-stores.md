---
title: Configuring preference stores
weight: 1
---

This page explains how to configure where preferences are stored.

In this package, `withDriver()` selects a store name (for example `shared` or `column`). That store maps to a driver configuration in `config/model-preferences.php`.

## Available drivers

- `column`: stores all preferences in a JSON column on the model table.
- `table`: stores one row per preference in a dedicated table for each model type. You must set the table name per model with `withTable()`.
- `shared`: stores preferences for multiple model types in one polymorphic table.

`shared` is the default store.

## Configure the default store

Set the default store in `config/model-preferences.php`:

```php
use HosmelQ\ModelPreferences\Enums\StoreDriver;

'default' => env('MODEL_PREFERENCES_DRIVER', StoreDriver::Shared->value),
```

`MODEL_PREFERENCES_DRIVER` accepts `column`, `table`, or `shared`.

## Configure stores

Store definitions live in the `stores` array in `config/model-preferences.php`.

Use this section to configure shared options like connection and column name.

For the `table` driver, define the table name per model with `->withTable('...')`.

## Configure via environment variables

- `MODEL_PREFERENCES_DRIVER`: `column`, `table`, or `shared`.
- `MODEL_PREFERENCES_COLUMN_NAME`: JSON column name used by the `column` driver.
- `MODEL_PREFERENCES_DB_CONNECTION`: database connection for `table` and `shared`.
- `MODEL_PREFERENCES_TABLE`: shared table name used by the `shared` driver.

## Override the store per model

You can override the global default in `preferencesConfig()`:

```php
use HosmelQ\ModelPreferences\Support\PreferencesConfig;

public function preferencesConfig(): PreferencesConfig
{
    return PreferencesConfig::configure()
        ->withDriver('table')
        ->withTable('user_preferences');
}
```

## Generate a table migration

If you use the `table` driver, generate a migration for the model-specific preferences table:

```bash
php artisan model-preferences:table name_of_the_preferences_table
```

## Choosing a strategy

- Choose `column` when preferences should live directly on the model row.
- Choose `table` when each model type needs a dedicated table.
- Choose `shared` when you prefer one polymorphic table for many model types.

