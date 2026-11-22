---
title: "Testing"
description: "Use the in-memory fake driver when testing applications that use model preferences."
weight: 5
---

Call `Preferences::fake()` in your tests to keep preferences for every model in memory instead of reading from or writing to the database:

```php
use HosmelQ\ModelPreferences\Facades\Preferences;

Preferences::fake();

$user = User::factory()->create();
$user->preferences()->set('theme', Theme::Dark);

expect($user->preferences()->get('theme'))->toBe(Theme::Dark);
```

The fake supports defaults, validation, casts, events, eager loading, model deletion cleanup, and `wherePreference()` queries.
