---
title: "Custom Drivers"
description: "Implement and register custom preference storage drivers."
weight: 4
---

Implement the `HosmelQ\ModelPreferences\Contracts\PreferenceDriver` contract:

```php
namespace App\Preferences;

use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Contracts\PreferenceDriver;
use HosmelQ\ModelPreferences\Support\PreferencesConfig;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RedisDriver implements PreferenceDriver
{
    public function all(HasPreferences&Model $model, PreferencesConfig $config): array
    {
        // ...
    }

    public function clear(HasPreferences&Model $model, PreferencesConfig $config): array
    {
        // ...
    }

    public function delete(HasPreferences&Model $model, PreferencesConfig $config, array $keys): void
    {
        // ...
    }

    public function load(array $models, PreferencesConfig $config): void
    {
        // ...
    }

    public function set(HasPreferences&Model $model, PreferencesConfig $config, array $values): void
    {
        // ...
    }

    public function whereMissing(Builder $query, PreferencesConfig $config, string $key): void
    {
        // ...
    }

    public function whereStored(Builder $query, PreferencesConfig $config, string $key, mixed $value): void
    {
        // ...
    }
}
```

Register your driver in a service provider:

```php
use App\Preferences\RedisDriver;
use HosmelQ\ModelPreferences\Facades\Preferences;

Preferences::extend('redis', fn () => new RedisDriver());
```

Then select it in your model's `preferencesConfig()`:

```php
return PreferencesConfig::configure()->withDriver('redis');
```
