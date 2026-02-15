---
title: Custom drivers
weight: 2
---

Implement `PreferenceDriver` or `PresenceAwarePreferenceDriver` when you need custom
storage behavior.

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

Register the driver through the facade:

```php
use HosmelQ\ModelPreferences\Facades\Preferences;
use HosmelQ\ModelPreferences\PreferencesStore;

Preferences::extend('redis', function () {
    return new PreferencesStore('redis', new RedisPreferenceDriver());
});
```

