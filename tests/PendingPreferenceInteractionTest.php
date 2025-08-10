<?php

declare(strict_types=1);

use HosmelQ\ModelPreferences\Tests\TestSupport\Enums\UserPreference;
use HosmelQ\ModelPreferences\Tests\TestSupport\Models\OtherUserPreferencesModel;
use HosmelQ\ModelPreferences\Tests\TestSupport\Models\SharedPreferencesModel;
use HosmelQ\ModelPreferences\Tests\TestSupport\Models\User;
use HosmelQ\ModelPreferences\Tests\TestSupport\Models\UserWithTablePreferences;

it('returns all preferences', function (): void {
    $user = User::create();

    $user->preferences()->setMultiple([
        'notifications' => false,
        'theme' => 'light',
    ]);

    expect($user->preferences()->all())->toBe([
        'notifications' => false,
        'theme' => 'light',
    ]);
});

it('removes all preferences', function (): void {
    $user = User::create();

    $user->preferences()->setMultiple([
        'notifications' => false,
        'theme' => 'light',
    ]);

    $user->preferences()->clear();

    expect($user->preferences()->all())->toBe([]);
});

it('removes a preference', function (BackedEnum|string $key, mixed $value): void {
    $user = User::create();

    $user->preferences()->set($key, $value);

    expect($user->preferences()->has($key))->toBeTrue();

    $user->preferences()->delete($key);

    expect($user->preferences()->has($key))->toBeFalse();
})->with('key variations');

it('removes multiple preferences', function (): void {
    $user = User::create();

    $user->preferences()->setMultiple([
        'notifications' => false,
        'theme' => 'dark',
    ]);

    $user->preferences()->deleteMultiple(['theme']);

    expect($user->preferences()->missing('theme'))->toBeTrue();
});

it('returns a preference value', function (BackedEnum|string $key, mixed $value): void {
    $user = User::create();

    $user->preferences()->set($key, $value);

    expect($user->preferences()->get($key))->toBe($value);
})->with('key variations');

it('returns a preference value for the column driver', function (): void {
    config(['model-preferences.default' => 'column']);

    $user = new User();

    $user->preferences()->set('theme', 'dark');

    expect($user->preferences()->get('theme'))->toBe('dark');
});

it('returns defaults for unset preferences', function (): void {
    $user = User::create();

    expect($user->preferences())
        ->get('notifications')->toBeTrue()
        ->get('theme')->toBe('system');
});

it('returns a custom default when preference does not exist', function (): void {
    $user = User::create();

    expect($user->preferences()->get('nonexistent', 'default'))->toBe('default');
});

it('returns null when no defaults are defined', function (): void {
    $model = SharedPreferencesModel::create();

    expect($model->preferences()->get('missing'))->toBeNull();
});

it('does not apply defaults when column preferences store null values', function (): void {
    config(['model-preferences.default' => 'column']);

    $user = new User();

    $user->setAttribute('preferences', ['theme' => null]);

    expect($user->preferences()->get('theme', 'system'))->toBeNull();
});

it('does not apply defaults when table preferences store null values', function (): void {
    config(['model-preferences.default' => 'table']);

    $user = UserWithTablePreferences::create();

    $user->preferences()->set('theme', null);

    expect($user->preferences()->get('theme'))->toBeNull();
});

it('returns null when preference is explicitly set to null', function (): void {
    $user = User::create();

    $user->preferences()->set('timezone', null);

    expect($user->preferences()->get('timezone', 'UTC'))->toBeNull();
});

it('returns multiple preference values', function (): void {
    $user = User::create();

    $user->preferences()->setMultiple([
        'notifications' => false,
        'theme' => 'dark',
    ]);

    expect($user->preferences()->getMultiple(['notifications', 'theme']))
        ->toBe(['notifications' => false, 'theme' => 'dark']);
});

it('returns defaults for missing values', function (): void {
    $user = User::create();

    $user->preferences()->setMultiple([
        'notifications' => false,
    ]);

    expect($user->preferences()->getMultiple(['notifications', 'theme']))
        ->toBe(['notifications' => false, 'theme' => 'system']);
});

it('returns true when preference exists', function (BackedEnum|string $key, mixed $value): void {
    $user = User::create();

    expect($user->preferences()->has($key))->toBeFalse();

    $user->preferences()->set($key, $value);

    expect($user->preferences()->has($key))->toBeTrue();
})->with('key variations');

it('returns true when preference is absent', function (): void {
    $user = User::create();

    expect($user->preferences()->missing('theme'))->toBeTrue();
});

it('stores a preference value', function (): void {
    $user = User::create();

    $user->preferences()->set('theme', 'dark');

    expect($user->preferences()->get('theme'))->toBe('dark');
});

it('validates preferences against rules', function (): void {
    $user = User::create();

    $user->preferences()->set('theme', 'invalid');
})->throws(InvalidArgumentException::class, 'The given preference data was invalid.');

it('scopes preferences per model', function (): void {
    $user = User::create();

    $other = OtherUserPreferencesModel::create();

    $other->preferences()->set('max_members', 50);
    $user->preferences()->set('theme', 'light');

    expect($other->preferences())
        ->get('max_members')->toBe(50)
        ->get('theme', 'default')->toBe('default')
        ->and($user->preferences())
        ->get('theme')->toBe('light')
        ->get('max_members', 'default')->toBe('default');
});

it('accepts enum keys', function (): void {
    $user = User::create();

    $user->preferences()->set(UserPreference::Theme, 'dark');

    expect($user->preferences()->all())
        ->theme->toBe('dark');
});

it('stores multiple preference values', function (): void {
    $user = User::create();

    $user->preferences()->setMultiple([
        'notifications' => false,
        'theme' => 'dark',
    ]);

    expect($user->preferences()->all())
        ->toBe(['notifications' => false, 'theme' => 'dark']);
});
