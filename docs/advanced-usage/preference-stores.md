---
title: "Preference Stores"
description: "Choose between the column, shared table, and per-model table stores."
weight: 1
---

Laravel Model Preferences ships with three database stores. Select a default store in `config/model-preferences.php` or override it per model with `withDriver()`.

## Column store

The `column` store keeps preferences in a JSON column on the model's table, which the model must cast to an array:

```php
Schema::table('users', function (Blueprint $table): void {
    $table->json('preferences')->nullable();
});
```

```php
protected function casts(): array
{
    return [
        'preferences' => 'array',
    ];
}
```

Array casts such as `array`, `json`, `AsArrayObject`, or `encrypted:array` (on a `text` column) are supported. Select a different column name with `withColumn()`.

Writes re-read the row under a lock so concurrent updates or partial selects (`User::select('id')->first()`) do not overwrite other preferences, and update the model's `updated_at` timestamp. While the preferences column itself has unsaved changes on the model, writes throw a `LogicException`.

## Shared table store

The `shared` store keeps preferences for all models in the polymorphic table created by the published migration. Its `preferable_id` column respects `Schema::morphUsingUuids()` and `Schema::morphUsingUlids()`.

Writes use a single `upsert`.

## Table store

The `table` store keeps preferences for a model in a dedicated table on the model's database connection, named after the model's table by default (`user_preferences` for `users`). Select a different table name with `withTable()`.

Generate the migration for a model with:

```bash
php artisan model-preferences:table "App\Models\User"
```

The generated migration runs on the model's connection, uses `foreignIdFor()` to match the model's key type, and adds a cascading foreign key.
