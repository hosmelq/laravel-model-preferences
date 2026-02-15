---
title: Retrieving values
weight: 2
---

Use the preference interaction returned by `$model->preferences()`.

## Retrieving a value

```php
$theme = $user->preferences()->get('theme');
$theme = $user->preferences()->get('theme', 'light');
```

## Multiple values

```php
$values = $user->preferences()->getMultiple([
    'theme',
    'newsletter',
]);
```

## Setting values

```php
$user->preferences()->set('theme', 'dark');
$user->preferences()->setMultiple([
    'theme' => 'light',
    'newsletter' => false,
]);
```

## Presence and defaults

- `has()` / `missing()` checks a key exists.
- If a key is absent, `get()` uses model defaults, then the provided default, then `null`.

