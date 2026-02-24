# Laravel Model Preferences

[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/hosmelq/laravel-model-preferences/ci.yml?branch=main&label=tests&style=flat-square)](https://github.com/hosmelq/laravel-model-preferences/actions/workflows/ci.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/hosmelq/laravel-model-preferences.svg?style=flat-square)](https://packagist.org/packages/hosmelq/laravel-model-preferences)
[![Total Downloads](https://img.shields.io/packagist/dt/hosmelq/laravel-model-preferences.svg?style=flat-square)](https://packagist.org/packages/hosmelq/laravel-model-preferences)

This package provides a simple API for storing preferences on Eloquent models.

You can define defaults and validation rules, and choose where preferences are stored.

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

$user = User::query()->first();

$user->preferences()->set('theme', 'dark');

$theme = $user->preferences()->get('theme');
```

You can customize defaults, validation rules, and storage options:

```php
use HosmelQ\ModelPreferences\Support\PreferencesConfig;

public function preferencesConfig(): PreferencesConfig
{
    return PreferencesConfig::configure()
        ->withColumn('settings')
        ->withDefaults([
            'theme' => 'system',
        ])
        ->withDriver('column')
        ->withRules([
            'theme' => ['in:light,dark,system'],
        ]);
}
```

## Documentation

All documentation is available [on the documentation site](https://hosmelq.com/docs/laravel-model-preferences).

## Testing

```bash
composer test
```

## Support

If this package is useful in your project, consider starring the repository and sharing feedback or improvements.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Contributions are welcome. Please open a PR with tests and clear context for the change.

## Security

If you discover any security related issues, please email hosmelq@gmail.com instead of using the issue tracker.

## Credits

- [Hosmel Quintana](https://github.com/hosmelq)
- [All Contributors](https://github.com/hosmelq/laravel-model-preferences/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
