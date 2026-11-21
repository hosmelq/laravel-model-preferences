---
title: "Events"
description: "Listen for PreferenceUpdated and PreferenceDeleted events."
weight: 2
---

Laravel Model Preferences dispatches two events in the `HosmelQ\ModelPreferences\Events` namespace:

- `PreferenceUpdated($model, $values)` — dispatched when preferences are stored via `set()` or `setMultiple()`.
- `PreferenceDeleted($model, $keys)` — dispatched when preferences are removed via `delete()`, `deleteMultiple()`, or `clear()`.

Both events implement `Illuminate\Contracts\Events\ShouldDispatchAfterCommit`, so listeners run only after the active database transaction commits.

```php
use HosmelQ\ModelPreferences\Events\PreferenceUpdated;
use Illuminate\Support\Facades\Event;

Event::listen(PreferenceUpdated::class, function (PreferenceUpdated $event): void {
    // $event->model
    // $event->values
});
```
