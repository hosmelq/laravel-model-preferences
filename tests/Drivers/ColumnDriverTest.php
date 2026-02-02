<?php

declare(strict_types=1);

use HosmelQ\ModelPreferences\Drivers\ColumnDriver;
use HosmelQ\ModelPreferences\Exceptions\PreferenceColumnNotFound;
use HosmelQ\ModelPreferences\Tests\TestSupport\Models\User;
use HosmelQ\ModelPreferences\Tests\TestSupport\Models\UserWithInvalidColumn;

beforeEach(function (): void {
    config(['model-preferences.default' => 'column']);
});

it('returns empty results when no preferences exist', function (): void {
    $driver = new ColumnDriver();
    $user = User::create();

    expect($driver->all($user))->toBe([]);
});

it('returns empty results when preferences column is not an array', function (): void {
    $driver = new ColumnDriver();
    $user = User::create();

    $user->setAttribute('preferences', 'invalid');

    expect($driver->all($user))->toBe([]);
});

it('removes all preferences', function (): void {
    $driver = new ColumnDriver();
    $user = User::create();

    $driver->setMultiple($user, [
        'notifications' => true,
        'theme' => 'dark',
    ]);

    $driver->clear($user);

    expect($driver->all($user))->toBe([]);
});

it('removes a preference', function (): void {
    $driver = new ColumnDriver();
    $user = User::create();

    $driver->setMultiple($user, [
        'notifications' => true,
        'theme' => 'dark',
    ]);

    $driver->delete($user, 'theme');

    expect($driver->has($user, 'theme'))->toBeFalse();
});

it('removes multiple preferences', function (): void {
    $driver = new ColumnDriver();
    $user = User::create();

    $driver->setMultiple($user, [
        'notifications' => true,
        'theme' => 'dark',
    ]);

    $driver->deleteMultiple($user, ['theme', 'notifications']);

    expect($driver->all($user))->toBe([]);
});

it('returns a preference value', function (): void {
    $driver = new ColumnDriver();
    $user = User::create();

    $driver->set($user, 'theme', 'dark');

    expect($driver->get($user, 'theme'))->toBe('dark');
});

it('returns multiple preference values', function (): void {
    $driver = new ColumnDriver();
    $user = User::create();

    $driver->setMultiple($user, [
        'notifications' => true,
        'theme' => 'dark',
    ]);

    expect($driver->getMultiple($user, ['theme', 'notifications']))
        ->toBe(['theme' => 'dark', 'notifications' => true]);
});

it('returns true when preference exists', function (): void {
    $driver = new ColumnDriver();
    $user = User::create();

    $driver->set($user, 'theme', 'dark');

    expect($driver->has($user, 'theme'))->toBeTrue();
});

it('returns true when preference is absent', function (): void {
    $driver = new ColumnDriver();
    $user = User::create();

    expect($driver->missing($user, 'theme'))->toBeTrue();
});

it('stores a preference value', function (): void {
    $driver = new ColumnDriver();
    $user = User::create();

    $driver->set($user, 'theme', 'dark');

    expect($driver->get($user, 'theme'))->toBe('dark');
});

it('stores multiple preference values', function (): void {
    $driver = new ColumnDriver();
    $user = User::create();

    $driver->setMultiple($user, [
        'notifications' => true,
        'theme' => 'dark',
    ]);

    expect($driver->all($user))->toBe(['notifications' => true, 'theme' => 'dark']);
});

it('throws when the configured column is invalid', function (): void {
    $driver = new ColumnDriver();

    $model = UserWithInvalidColumn::create();

    $driver->all($model);
})->throws(PreferenceColumnNotFound::class, 'The preferences column is not configured for the [users] table.');
