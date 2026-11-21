---
title: "Upgrading From 1.x"
description: "Breaking changes and migration steps when upgrading from Laravel Model Preferences 1.x to 2.0."
weight: 4
---

## Requirements

- PHP 8.4 or later and Laravel 13 are now required.

## Column store models

- Models using the `column` store must cast their preferences column to an array (for example, `'preferences' => 'array'`).

## Repository and configuration

- `$model->preferences()` now returns a `PreferenceRepository` instead of `PendingPreferenceInteraction`, and the `HasPreferences` contract declares it.
- `PreferencesConfig` properties are now private; use the getter methods instead. `getColumn()` returns `null` when the model uses the configured default column.
- `all()` now merges stored values over configured defaults. Use `has()` to check whether a preference is explicitly stored.
- A default passed to `get($key, $default)` now takes precedence over the configured default.
- Passing overlapping parent and child keys (such as `notifications` and `notifications.email`) in a single `setMultiple()` call throws an `InvalidArgumentException`.
- Modifying preferences on an unsaved model throws a `LogicException`.
- Reading preferences on a model selected without its primary key throws a `MissingAttributeException`, unless the model uses the `column` store and its preferences column was selected.

## Validation and exceptions

- `PreferenceValidationException` now extends Laravel's `Illuminate\Validation\ValidationException`.
- `PreferenceColumnNotFound` and `PreferenceTableNotFound` were removed.

## Stores and migrations

- The `PreferenceDriver` contract was simplified: `PreferencesStore`, `PresenceAwarePreferenceDriver`, and `PreferenceRead` were removed, and `Preferences::extend()` callbacks now return a `PreferenceDriver` directly.
- The `table` store uses the model's database connection, so `stores.table.connection` was removed from the config file.
- `withTable()` is now optional for the `table` store; when omitted, the table name defaults to the singular model table name with a `_preferences` suffix (`user_preferences` for `users`). Existing 1.x models that already call `withTable()` continue working unchanged.
- `php artisan model-preferences:table` now takes a model class instead of a table name.
- Newly generated migrations omit timestamps and make `value` non-nullable. Existing 1.x tables continue to work.
- Models using `SoftDeletes` keep their preferences until they are force deleted.
