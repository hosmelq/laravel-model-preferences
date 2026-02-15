---
title: Preparing your model
weight: 1
---

A model uses this package by implementing `HasPreferences` and using the
`InteractsWithPreferences` trait.

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
        return PreferencesConfig::configure()
            ->withDefaults([
                'theme' => 'system',
            ])
            ->withRules([
                'theme' => ['in:light,dark,system'],
            ]);
    }
}
```

You can switch drivers per model:

```php
return PreferencesConfig::configure()
    ->withDriver('column')
    ->withColumn('settings');
```

- `column`: stores preferences in one JSON column
- `table`: stores one row per preference in a dedicated table
- `shared`: stores preferences in one polymorphic table

