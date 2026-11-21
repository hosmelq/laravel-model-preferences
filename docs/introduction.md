---
title: "Introduction"
description: "Store per-model preferences in Laravel with defaults, validation, casts, eager loading, and query scopes."
weight: 1
---

Laravel Model Preferences stores small per-model settings—such as themes, notification options, or feature toggles—alongside your Eloquent models.

Preferences can live in:

- A **JSON column** on the model's table (`column` store).
- A **shared polymorphic table** across all models (`shared` store).
- A **dedicated table per model** (`table` store).
- A **custom driver** registered with `Preferences::extend()`.

## Quick start

After [installing the package](installation), implement `HasPreferences` and use `InteractsWithPreferences` on your model:

```php
use App\Enums\Theme;
use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Models\Concerns\InteractsWithPreferences;
use HosmelQ\ModelPreferences\Support\PreferencesConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class User extends Model implements HasPreferences
{
    use InteractsWithPreferences;

    public function preferencesConfig(): PreferencesConfig
    {
        return PreferencesConfig::configure()
            ->withDefaults([
                'digest' => 'weekly',
                'theme' => Theme::System,
            ])
            ->withRules([
                'digest' => ['in:daily,weekly,never'],
                'theme' => [Rule::enum(Theme::class)],
            ])
            ->withCasts([
                'theme' => Theme::class,
            ]);
    }
}
```

Read, update, and query preferences directly through the model:

```php
$user->preferences()->set('theme', Theme::Dark);
$user->preferences()->get('theme'); // Theme::Dark
$user->preferences()->get('digest'); // 'weekly'

$users = User::query()
    ->wherePreference('digest', 'weekly')
    ->get()
    ->loadPreferences();
```

## Next steps

- [Defining preferences](basic-usage/defining-preferences) covers defaults, validation rules, strict mode, and casts.
- [Using preferences](basic-usage/using-preferences) covers reading, storing, deleting, and enum keys.
- [Preference stores](advanced-usage/preference-stores) compares the column, shared table, and per-model table stores.
- [Testing](basic-usage/testing) replaces database storage with an in-memory fake.
