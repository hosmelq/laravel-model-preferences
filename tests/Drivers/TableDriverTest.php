<?php

declare(strict_types=1);

use function Pest\Laravel\expectsDatabaseQueryCount;

use HosmelQ\ModelPreferences\Drivers\TableDriver;
use HosmelQ\ModelPreferences\Exceptions\PreferenceTableNotFound;
use HosmelQ\ModelPreferences\Tests\TestSupport\Models\User;
use HosmelQ\ModelPreferences\Tests\TestSupport\Models\UserWithInvalidTable;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    config(['model-preferences.default' => 'table']);
});

it('returns empty results when no preferences exist', function (): void {
    $driver = new TableDriver(resolve('db'));
    $user = User::create();

    expect($driver->all($user))->toBe([]);
});

it('returns null values stored in the table', function (): void {
    $driver = new TableDriver(resolve('db'));
    $user = User::create();

    DB::table('users_preferences')->insert([
        'model_id' => $user->getKey(),
        'key' => 'nullable',
        'value' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect($driver->all($user))->toBe(['nullable' => null]);
});

it('removes all preferences', function (): void {
    $driver = new TableDriver(resolve('db'));
    $user = User::create();

    $driver->setMultiple($user, [
        'notifications' => true,
        'theme' => 'dark',
    ]);

    $driver->clear($user);

    expect($driver->all($user))->toBe([]);
});

it('removes a preference', function (): void {
    $driver = new TableDriver(resolve('db'));
    $user = User::create();

    $driver->setMultiple($user, [
        'notifications' => true,
        'theme' => 'dark',
    ]);

    $driver->delete($user, 'theme');

    expect($driver->has($user, 'theme'))->toBeFalse();
});

it('removes multiple preferences', function (): void {
    $driver = new TableDriver(resolve('db'));
    $user = User::create();

    $driver->setMultiple($user, [
        'notifications' => true,
        'theme' => 'dark',
    ]);

    $driver->deleteMultiple($user, ['theme', 'notifications']);

    expect($driver->all($user))->toBe([]);
});

it('returns a preference value', function (): void {
    $driver = new TableDriver(resolve('db'));
    $user = User::create();

    $driver->set($user, 'theme', 'dark');

    expect($driver->get($user, 'theme'))->toBe('dark');
});

it('returns multiple preference values', function (): void {
    $driver = new TableDriver(resolve('db'));
    $user = User::create();

    $driver->setMultiple($user, [
        'notifications' => true,
        'theme' => 'dark',
    ]);

    expect($driver->getMultiple($user, ['theme', 'notifications']))
        ->toEqual(['theme' => 'dark', 'notifications' => true]);
});

it('returns null values for missing records', function (): void {
    $driver = new TableDriver(resolve('db'));
    $user = User::create();

    DB::table('users_preferences')->insert([
        'model_id' => $user->getKey(),
        'key' => 'nullable',
        'value' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect($driver->getMultiple($user, ['nullable']))->toBe(['nullable' => null]);
});

it('returns missing when no record exists', function (): void {
    $driver = new TableDriver(resolve('db'));
    $user = User::create();

    $read = $driver->getWithPresence($user, 'missing');

    expect($read->exists)->toBeFalse()
        ->and($read->value)->toBeNull();
});

it('returns null when the stored value is null', function (): void {
    $driver = new TableDriver(resolve('db'));
    $user = User::create();

    DB::table('users_preferences')->insert([
        'model_id' => $user->getKey(),
        'key' => 'nullable',
        'value' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $read = $driver->getWithPresence($user, 'nullable');

    expect($read->exists)->toBeTrue()
        ->and($read->value)->toBeNull();
});

it('returns true when preference exists', function (): void {
    $driver = new TableDriver(resolve('db'));
    $user = User::create();

    $driver->set($user, 'theme', 'dark');

    expect($driver->has($user, 'theme'))->toBeTrue();
});

it('returns true when preference is absent', function (): void {
    $driver = new TableDriver(resolve('db'));
    $user = User::create();

    expect($driver->missing($user, 'theme'))->toBeTrue();
});

it('stores a preference value', function (): void {
    $driver = new TableDriver(resolve('db'));
    $user = User::create();

    $driver->set($user, 'theme', 'dark');

    expect($driver->get($user, 'theme'))->toBe('dark');
});

it('stores multiple preference values', function (): void {
    $driver = new TableDriver(resolve('db'));
    $user = User::create();

    $driver->setMultiple($user, [
        'notifications' => true,
        'theme' => 'dark',
    ]);

    expect($driver->all($user))->toBe(['notifications' => true, 'theme' => 'dark']);
});

it('stores multiple preference values in a single query', function (): void {
    $driver = new TableDriver(resolve('db'));
    $user = User::create();

    expectsDatabaseQueryCount(1);

    $driver->setMultiple($user, [
        'notifications' => true,
        'theme' => 'dark',
    ]);
});

it('throws when the configured table name is invalid', function (): void {
    $driver = new TableDriver(resolve('db'));

    $model = UserWithInvalidTable::create();

    $driver->all($model);
})->throws(PreferenceTableNotFound::class, 'The preferences table is not configured for the [users] table.');
