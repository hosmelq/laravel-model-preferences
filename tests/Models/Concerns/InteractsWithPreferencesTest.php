<?php

declare(strict_types=1);

use HosmelQ\ModelPreferences\Exceptions\PreferenceValidationException;
use HosmelQ\ModelPreferences\Models\Preference;
use Workbench\App\Enums\UserPreference;
use Workbench\App\Models\Team;
use Workbench\App\Models\User;

it('deletes preference', function (BackedEnum|string $key, mixed $value): void {
    $user = User::create();

    $user->setPreference($key, $value);

    expect($user->hasPreference($key))->toBeTrue();

    $user->deletePreference($key);

    expect($user->hasPreference($key))->toBeFalse();
})->with('key variations');

it('deletes multiple preferences', function (): void {
    $user = User::create();

    $user->setPreferences([
        'notifications' => false,
        'theme' => 'light',
    ]);

    $user->deletePreferences(['notifications', UserPreference::Theme]);

    expect($user)
        ->hasPreference('notifications')->toBeFalse()
        ->hasPreference('theme')->toBeFalse();
});

it('deletes preferences when model is deleted', function (): void {
    $user = User::create();

    $user->setPreferences([
        'notifications' => false,
        'theme' => 'light',
    ]);

    expect($user->preferences)->toHaveCount(2);

    $user->delete();

    expect(Preference::query()
        ->where('preferable_id', $user->id)
        ->where('preferable_type', User::class)
        ->get())->toHaveCount(0);
});

it('gets all preferences merged with defaults', function (): void {
    $user = User::create();

    $user->setPreferences([
        'theme' => 'light',
    ]);

    $allPreferences = $user->allPreferences();

    expect($allPreferences)->toEqual([
        'notifications' => true,
        'theme' => 'light',
    ]);
});

it('checks if preference exists', function (BackedEnum|string $key, mixed $value): void {
    $user = User::create();

    expect($user->hasPreference($key))->toBeFalse();

    $user->setPreference($key, $value);

    expect($user->hasPreference($key))->toBeTrue();
})->with('key variations');

it('loads specific preferences', function (): void {
    $user = User::create();

    $user->setPreferences([
        'extra' => 'value',
        'notifications' => false,
        'theme' => 'light',
    ]);

    $user->loadPreferences(['notifications', UserPreference::Theme]);

    expect($user)
        ->relationLoaded('preferences')->toBeTrue()
        ->preferences->toHaveCount(2)
        ->preferences->pluck('key')->toArray()->toEqualCanonicalizing(['notifications', 'theme']);
});

it('loads all preferences when no keys specified', function (): void {
    $user = User::create();

    $user->setPreferences([
        'notifications' => false,
        'theme' => 'light',
    ]);

    $user->loadPreferences();

    expect($user->preferences)->toHaveCount(2);
});

it('gets preference value', function (BackedEnum|string $key, mixed $value): void {
    $user = User::create();

    $user->setPreference($key, $value);

    expect($user->preference($key))->toBe($value);
})->with('key variations');

it('returns defaults for unset preferences', function (): void {
    $user = User::create();

    expect($user)
        ->preference('notifications')->toBeTrue()
        ->preference('theme')->toBe('system');
});

it('returns custom default when preference does not exist', function (): void {
    $user = User::create();

    expect($user->preference('nonexistent', 'default'))->toBe('default');
});

it('handles array preferences', function (string $key, array $value): void {
    $user = User::create();

    $user->setPreference($key, $value);

    expect($user->preference($key))->toEqual($value);
})->with('complex values');

it('sets multiple preferences', function (): void {
    $user = User::create();

    $user->setPreferences([
        'notifications' => false,
        'theme' => 'light',
    ]);

    expect($user)
        ->preference('notifications')->toBeFalse()
        ->preference('theme')->toBe('light');
});

it('validates preferences against rules', function (): void {
    $user = User::create();

    $user->setPreference('theme', 'invalid');
})->throws(PreferenceValidationException::class, 'The given preference data was invalid.');

it('handles preferences for different models independently', function (): void {
    $team = Team::create();
    $user = User::create();

    $team->setPreference('max_members', 50);
    $user->setPreference('theme', 'light');

    expect($team)
        ->preference('max_members')->toBe(50)
        ->preference('theme', 'default')->toBe('default')
        ->and($user)
        ->preference('theme')->toBe('light')
        ->preference('max_members', 'default')->toBe('default');
});
