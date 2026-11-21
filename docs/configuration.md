---
title: "Configuration"
description: "Configure the default preference store and store-specific options."
weight: 3
---

The published `config/model-preferences.php` file defines the default store and the settings for each store.

## Default store

Models that do not call `withDriver()` in `preferencesConfig()` use the default store (`shared` by default). Supported built-in values are `column`, `shared`, and `table`.

## Store options

| Option | Environment variable | Default | Description |
| --- | --- | --- | --- |
| `default` | `MODEL_PREFERENCES_DRIVER` | `shared` | Default driver when a model does not select one. |
| `stores.column.name` | `MODEL_PREFERENCES_COLUMN_NAME` | `preferences` | Default JSON column name for the `column` store. |
| `stores.shared.connection` | `MODEL_PREFERENCES_DB_CONNECTION` | `null` | Database connection for the `shared` store (`null` uses the default connection). |
| `stores.shared.table` | `MODEL_PREFERENCES_TABLE` | `preferences` | Table name for the `shared` store. |

The `table` store always uses the database connection of the model itself and derives its table name from the model's table unless overridden with `withTable()`. See [preference stores](advanced-usage/preference-stores).
