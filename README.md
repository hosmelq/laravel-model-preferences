# Laravel Model Preferences

A Laravel package for managing model preferences with validation.

## Features

- **Model preferences** - Any Eloquent model can have preferences.
- **Enum key support** - Use PHP enums as preference keys for better type safety.
- **Default values** - Set default values for undefined preferences.
- **Validation support** - Define Laravel validation rules for preference values.

## Requirements

- PHP 8.2+
- Laravel 11.0+

## Installation

```bash
composer require hosmelq/laravel-model-preferences
```

## Configuration

The service provider will be automatically registered. You can use the install command for quick setup:

```bash
php artisan model-preferences:install
```

This command will publish the migrations, config file, and ask to run migrations automatically.

Alternatively, you can publish and run manually:

```bash
php artisan vendor:publish --tag="model-preferences-migrations"

php artisan migrate
```

Optionally, publish the config file:

```bash
php artisan vendor:publish --tag="model-preferences-config"
```

## Basic Usage

Implement the interface and add the trait to any Eloquent model:

```php
<?php

use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Models\Concerns\InteractsWithPreferences;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements HasPreferences
{
    use InteractsWithPreferences;
    
    public function preferenceDefaults(): array
    {
        return [
            'notifications' => true,
            'theme' => 'system',
        ];
    }
}
```

Set and get preferences:

```php
$user = User::find(1);

// Set a preference
$user->setPreference('theme', 'light');

// Get a preference
$theme = $user->preference('theme'); // 'light'

// Get with custom default
$theme = $user->preference('theme', 'dark');
```

## Usage

### Setting Up Models

Implement the `HasPreferences` interface, use the `InteractsWithPreferences` trait, and optionally define default preferences and validation rules:

```php
<?php

use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Models\Concerns\InteractsWithPreferences;
use Illuminate\Database\Eloquent\Model;

class Team extends Model implements HasPreferences
{
    use InteractsWithPreferences;
    
    public function preferenceDefaults(): array
    {
        return [
            'max_members' => 10,
            'visibility' => 'private',
        ];
    }
}
```

### Setting Preferences

Set individual preferences:

```php
$team = Team::find(1);

$team->setPreference('max_members', 50);
$team->setPreference(TeamPreference::Visibility, 'public'); // Using enum
```

Set multiple preferences at once:

```php
$team->setPreferences([
    'max_members' => 100,
    'visibility' => 'public',
]);
```

### Getting Preferences

Get individual preferences:

```php
$notifications = $user->preference('notifications');  // Returns stored value or default
$theme = $user->preference(UserPreference::Theme, 'light'); // Custom default using enum
```

**Default Value Priority:**
1. Stored preference value.
2. Value from `preferenceDefaults()` method (if reference exists).
3. Method parameter default (only used if reference not in `preferenceDefaults()`).

Get all preferences merged with defaults:

```php
$preferences = $user->allPreferences();
```

Check if a preference exists:

```php
if ($user->hasPreference('notifications')) {
    // Handle preference
}

if ($user->hasPreference(UserPreference::Theme)) { // Using enum
    // Handle preference
}
```


### Deleting Preferences

Delete individual preferences:

```php
$user->deletePreference('notifications');
$user->deletePreference(UserPreference::Theme); // Using enum
```

Delete multiple preferences:

```php
$user->deletePreferences([
    'notifications',
    UserPreference::Theme // Using enum
]);
```

### Eager Loading Preferences

Eager load preferences into the model's relationship cache to avoid N+1 database queries. When you call `preference()` later, Laravel can optimize these calls using the cached relationship data instead of hitting the database each time:

```php
// Load specific preferences
$user->loadPreferences([
    'notifications', 
    UserPreference::Theme // Using enum
]);

// Load all preferences
$user->loadPreferences();

// Now accessing these preferences won't trigger additional queries
$notifications = $user->preference('notifications');
$theme = $user->preference(UserPreference::Theme);
```

### Validation

The package uses Laravel's validation system to validate preference values. Validation occurs automatically when calling `setPreference()` or `setPreferences()` using the rules defined in your `preferenceRules()` method.

When validation fails, a `PreferenceValidationException` is thrown, which has an `errors()` method to access validation messages.

Define Laravel validation rules for preferences:

```php
<?php

use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Models\Concerns\InteractsWithPreferences;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class User extends Model implements HasPreferences
{
    use InteractsWithPreferences;
    
    public function preferenceRules(): array
    {
        return [
            'notifications' => ['boolean'],
            'theme' => [Rule::in(['dark', 'light', 'system'])],
        ];
    }
}
```

Handle validation errors when setting preferences:

```php
<?php

use HosmelQ\ModelPreferences\Exceptions\PreferenceValidationException;

try {
    $user->setPreference('theme', 'invalid-theme');
} catch (PreferenceValidationException $e) {
    $errors = $e->errors();
}
```


## Testing

```bash
composer test
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a list of changes.

## Credits

- [Hosmel Quintana](https://github.com/hosmelq)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
