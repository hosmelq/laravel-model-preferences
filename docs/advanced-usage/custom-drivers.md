---
title: Custom drivers
weight: 2
---

Use a custom driver when preferences should be stored in a backend that is not covered by the built-in drivers.

## Implement the driver contract

Implement `PreferenceDriver` for custom storage behavior.

If your driver can distinguish between "missing" and "stored null", implement `PresenceAwarePreferenceDriver` instead.

```php
use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Contracts\PreferenceDriver;

class RedisPreferenceDriver implements PreferenceDriver
{
    public function all(HasPreferences $model): array {}
    public function clear(HasPreferences $model): void {}
    public function delete(HasPreferences $model, string $key): void {}
    public function deleteMultiple(HasPreferences $model, array $keys): void {}
    public function get(HasPreferences $model, string $key): mixed {}
    public function getMultiple(HasPreferences $model, array $keys): array {}
    public function has(HasPreferences $model, string $key): bool {}
    public function missing(HasPreferences $model, string $key): bool {}
    public function set(HasPreferences $model, string $key, mixed $value): void {}
    public function setMultiple(HasPreferences $model, array $values): void {}
}
```

## Register a store

Register your driver through the facade:

```php
use HosmelQ\ModelPreferences\Facades\Preferences;
use HosmelQ\ModelPreferences\PreferencesStore;

Preferences::extend('redis', function () {
    return new PreferencesStore('redis', new RedisPreferenceDriver());
});
```

The name you register (`redis`) is the value you can pass to `withDriver()`.

## Use the custom store on a model

```php
use HosmelQ\ModelPreferences\Support\PreferencesConfig;

public function preferencesConfig(): PreferencesConfig
{
    return PreferencesConfig::configure()->withDriver('redis');
}
```

