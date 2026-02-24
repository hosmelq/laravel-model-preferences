---
title: Introduction
weight: 1
---

This package stores preferences on Eloquent models.

You can define defaults and validation rules, then choose where preferences are stored (`shared`, `table`, or `column`).

Here's a quick example:

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
        return PreferencesConfig::configure()->withDefaults([
            'theme' => 'system',
        ]);
    }
}

$user = User::query()->firstOrFail();

$user->preferences()->set('theme', 'dark');

$theme = $user->preferences()->get('theme');
```

By default, the package uses the `shared` driver.

The package supports `shared`, `table`, and `column` drivers.

