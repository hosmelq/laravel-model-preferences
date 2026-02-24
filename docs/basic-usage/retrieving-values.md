---
title: Retrieving values
weight: 2
---

Use the preference interaction returned by `$model->preferences()`.

## Retrieve a value

```php
$theme = $user->preferences()->get('theme');
$theme = $user->preferences()->get('theme', 'light');
```

## Retrieve multiple values

```php
$values = $user->preferences()->getMultiple([
    'newsletter',
    'theme',
]);
```

## Set values

```php
$user->preferences()->set('theme', 'dark');

$user->preferences()->setMultiple([
    'newsletter' => false,
    'theme' => 'light',
]);
```

## Check presence and defaults

- `has()` and `missing()` let you check if a key exists.
- If a key is missing, `get()` resolves values in this order: model defaults, provided fallback, then `null`.

