---
title: Introduction
weight: 1
---

This package stores preferences on Eloquent models.
It supports defaults, validation, and multiple storage strategies so you can pick the
best persistence approach per model.

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

## Next steps

- [Installation & setup](installation-setup.md) for installation and config publishing.
- [Requirements](requirements.md) for PHP/Laravel support and package requirements.
- [Preparing your model](basic-usage/preparing-your-model.md) for `HasPreferences` and `InteractsWithPreferences`.
- [Retrieving values](basic-usage/retrieving-values.md) for common API usage.
- [Advanced usage](advanced-usage/configuring-preference-stores.md) for driver and migration behavior.

## We have badges!

<section class="article_badges">
    <a href="https://packagist.org/packages/hosmelq/laravel-model-preferences"><img src="https://img.shields.io/packagist/dt/hosmelq/laravel-model-preferences.svg?style=flat-square" alt="Total Downloads"></a>
    <a href="https://github.com/hosmelq/laravel-model-preferences/releases"><img src="https://img.shields.io/github/release/hosmelq/laravel-model-preferences.svg?style=flat-square" alt="Latest Version"></a>
    <a href="https://github.com/hosmelq/laravel-model-preferences/blob/main/LICENSE.md"><img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="Software License"></a>
</section>
