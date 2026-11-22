---
title: "Defining Preferences"
description: "Configure preference stores, defaults, validation rules, strict mode, and casts on Eloquent models."
weight: 1
---

Implement the `HasPreferences` contract and use the `InteractsWithPreferences` trait on your model. Override `preferencesConfig()` to configure the store, defaults, validation rules, and casts:

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
            ->withDriver('table')
            ->withDefaults([
                'digest' => 'weekly',
                'theme' => Theme::System,
            ])
            ->withRules([
                'digest' => ['in:daily,weekly,never'],
                'notifications.email' => ['boolean'],
                'theme' => [Rule::enum(Theme::class)],
            ])
            ->withCasts([
                'snoozed_until' => 'immutable_datetime',
                'theme' => Theme::class,
            ]);
    }
}
```

Models that do not call `withDriver()` use the default store configured in `config/model-preferences.php`. The configuration is built once per model instance.

## Defaults

Defaults are returned for preferences that are not stored. They are returned as declared, so declare them with the type you expect back.

## Validation rules

Rules use Laravel's validation rules and run whenever preferences are stored. Dotted and wildcard rules apply to matching keys (for example, `notifications.email` validates `email` inside `notifications`, and `labels.*` validates keys under `labels`). Preferences stored together with `setMultiple()` are validated together, so cross-field rules such as `same:pin` or `distinct` work as expected.

When validation fails, a `PreferenceValidationException` is thrown with all validation errors. It extends `Illuminate\Validation\ValidationException`, so HTTP requests render it like any other validation error.

## Strict mode

By default, any preference key may be stored. Call `strict()` to reject keys that have neither a default nor a validation rule, catching typos such as `thme`:

```php
return PreferencesConfig::configure()
    ->withDefaults(['theme' => 'system'])
    ->strict();
```

## Casts

Use `withCasts()` to apply Eloquent casts to preferences when they are stored and read, including enums, dates, `encrypted`, primitive casts (`boolean`, `integer`, `decimal:2`), collections (`AsCollection`), or custom casts.

Casts run on a stand-in model, so a custom cast cannot read attributes from the owner model.
