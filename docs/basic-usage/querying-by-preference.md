---
title: "Querying by Preference"
description: "Filter Eloquent queries by stored preference values and configured defaults."
weight: 4
---

The `wherePreference()` scope matches models whose stored preference—or configured default when not stored—equals the given value:

```php
$users = User::query()
    ->wherePreference('digest', 'weekly')
    ->get();
```

The scope works across all built-in stores (`column`, `shared`, and `table`) as well as the in-memory fake.

## Semantics

- JSON values are compared strictly: `true` does not match `1`, and `7` does not match `'7'`.
- Preference casts are applied to the queried value before querying, so pass the value in the same form you would pass to `set()` (for example, an enum case or a date).
- Models that do not store the preference are included when the queried value matches the configured default.
- Encrypted preferences cannot be queried with `wherePreference()`.

## Database support

`wherePreference()` supports:

- SQLite
- MySQL 8+
- MariaDB 10.7+
- PostgreSQL 12+
- SQL Server 2016+
