---
title: "Using Preferences"
description: "Read, store, check presence, delete, and serialize model preferences."
weight: 2
---

The `preferences()` method returns a `PreferenceRepository` for the model. Call it as a method: `$user->preferences` is not a relationship, and with the `column` store it accesses the raw column attribute.

Preferences belong to saved models. Changing preferences on an unsaved model throws a `LogicException`.

## Retrieving preferences

```php
$user->preferences()->get('theme');
$user->preferences()->get('timezone', 'UTC');
$user->preferences()->get('locale', fn () => app()->getLocale());
$user->preferences()->getMultiple(['theme', 'timezone']);
$user->preferences()->all();
```

`get()` returns the stored value even when it is `null`. When a preference is not stored, it falls back to the default passed to `get()` (if any), and then to the configured default.

`all()` and `getMultiple()` merge stored values over configured defaults.

The repository implements `Arrayable` and `JsonSerializable`, serializing cast preferences the same way Eloquent serializes cast attributes, so you can return `$user->preferences()` directly from a controller.

## Storing preferences

```php
$user->preferences()->set('theme', Theme::Dark);

$user->preferences()->setMultiple([
    'digest' => 'daily',
    'locale' => 'es',
]);
```

Do not pass overlapping parent and child keys (such as `notifications` and `notifications.email`) in the same `setMultiple()` call; doing so throws an `InvalidArgumentException`.

## Checking for presence

`has()` and `missing()` check whether a preference is explicitly stored, ignoring configured defaults:

```php
if ($user->preferences()->missing('timezone')) {
    // ...
}
```

## Deleting preferences

```php
$user->preferences()->delete('theme');
$user->preferences()->deleteMultiple(['theme', 'locale']);
$user->preferences()->clear();
```

## Enum keys

Methods that take preference keys accept enums, except `setMultiple()`, since PHP arrays cannot use enums as keys. Backed enums use their value and unit enums use their name:

```php
$user->preferences()->set(UserPreference::Theme, 'dark');
$user->preferences()->get(UserPreference::Theme);
```
