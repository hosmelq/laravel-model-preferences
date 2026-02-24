---
title: Preparing your model
weight: 1
---

A model uses this package by implementing `HasPreferences` and using the `InteractsWithPreferences` trait.

## Add the contract and trait

```php
use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Models\Concerns\InteractsWithPreferences;
use HosmelQ\ModelPreferences\Support\PreferencesConfig;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements HasPreferences
{
    use InteractsWithPreferences;

    public function preferencesConfig(): PreferencesConfig
    {
        return PreferencesConfig::configure();
    }
}
```

## Define defaults and validation rules

Use `preferencesConfig()` to define defaults and validation rules for your keys:

```php
public function preferencesConfig(): PreferencesConfig
{
    return PreferencesConfig::configure()
        ->withDefaults([
            'theme' => 'system',
        ])
        ->withRules([
            'theme' => ['in:light,dark,system'],
        ]);
}
```

## Choose a store for this model

You can override the default driver per model:

```php
public function preferencesConfig(): PreferencesConfig
{
    return PreferencesConfig::configure()
        ->withColumn('settings')
        ->withDriver('column');
}
```

Use `withDriver()`, `withColumn()`, and `withTable()` to control where preferences are stored.

