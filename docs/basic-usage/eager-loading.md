---
title: "Eager Loading"
description: "Avoid N+1 queries by eager loading preferences for collections of models."
weight: 3
---

Each model instance loads and caches its preferences once, so reading several preferences on a single model runs only one query.

To load the preferences of many models in a single query, call `loadPreferences()` on an Eloquent collection, or pass any iterable of models to `Preferences::load()`:

```php
use HosmelQ\ModelPreferences\Facades\Preferences;

$users = User::query()->get()->loadPreferences();

Preferences::load($comments->pluck('author'));
```

Eager loading works across all built-in stores (`shared`, `table`, and `column` for models retrieved without selecting their preferences column). Non-integer keys (such as UUIDs) are loaded in batches of 1,000 to stay within database parameter limits. Models without preferences or without a primary key are skipped automatically.

Loaded preferences stay cached per model instance. Call `fresh()` on a model if you need to re-read its preferences from the database in a new instance.
