<?php

declare(strict_types=1);

use function Pest\Laravel\expectsDatabaseQueryCount;

use HosmelQ\ModelPreferences\Drivers\SharedTableDriver;
use HosmelQ\ModelPreferences\Tests\TestSupport\Models\SharedPreferencesModel;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    config(['model-preferences.default' => 'shared']);
});

it('returns empty results when no preferences exist', function (): void {
    $driver = new SharedTableDriver(resolve('db'));
    $model = SharedPreferencesModel::create();

    expect($driver->all($model))->toBe([]);
});

it('returns null values stored in the shared table', function (): void {
    $driver = new SharedTableDriver(resolve('db'));
    $model = SharedPreferencesModel::create();

    DB::table('preferences')->insert([
        'preferable_type' => $model->getMorphClass(),
        'preferable_id' => $model->getKey(),
        'key' => 'nullable',
        'value' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect($driver->all($model))->toBe(['nullable' => null]);
});

it('removes all preferences', function (): void {
    $driver = new SharedTableDriver(resolve('db'));
    $model = SharedPreferencesModel::create();

    $driver->setMultiple($model, [
        'notifications' => true,
        'theme' => 'dark',
    ]);

    $driver->clear($model);

    expect($driver->all($model))->toBe([]);
});

it('removes a preference', function (): void {
    $driver = new SharedTableDriver(resolve('db'));
    $model = SharedPreferencesModel::create();

    $driver->setMultiple($model, [
        'notifications' => true,
        'theme' => 'dark',
    ]);

    $driver->delete($model, 'theme');

    expect($driver->has($model, 'theme'))->toBeFalse();
});

it('removes multiple preferences', function (): void {
    $driver = new SharedTableDriver(resolve('db'));
    $model = SharedPreferencesModel::create();

    $driver->setMultiple($model, [
        'notifications' => true,
        'theme' => 'dark',
    ]);

    $driver->deleteMultiple($model, ['theme', 'notifications']);

    expect($driver->all($model))->toBe([]);
});

it('returns a preference value', function (): void {
    $driver = new SharedTableDriver(resolve('db'));
    $model = SharedPreferencesModel::create();

    $driver->set($model, 'theme', 'dark');

    expect($driver->get($model, 'theme'))->toBe('dark');
});

it('returns multiple preference values', function (): void {
    $driver = new SharedTableDriver(resolve('db'));
    $model = SharedPreferencesModel::create();

    $driver->setMultiple($model, [
        'notifications' => true,
        'theme' => 'dark',
    ]);

    expect($driver->getMultiple($model, ['theme', 'notifications']))
        ->toEqual(['theme' => 'dark', 'notifications' => true]);
});

it('returns null values for missing records', function (): void {
    $driver = new SharedTableDriver(resolve('db'));
    $model = SharedPreferencesModel::create();

    DB::table('preferences')->insert([
        'preferable_type' => $model->getMorphClass(),
        'preferable_id' => $model->getKey(),
        'key' => 'nullable',
        'value' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect($driver->getMultiple($model, ['nullable']))->toBe(['nullable' => null]);
});

it('returns missing when no record exists', function (): void {
    $driver = new SharedTableDriver(resolve('db'));
    $model = SharedPreferencesModel::create();

    $read = $driver->getWithPresence($model, 'missing');

    expect($read->exists)->toBeFalse()
        ->and($read->value)->toBeNull();
});

it('returns null when the stored value is null', function (): void {
    $driver = new SharedTableDriver(resolve('db'));
    $model = SharedPreferencesModel::create();

    DB::table('preferences')->insert([
        'preferable_type' => $model->getMorphClass(),
        'preferable_id' => $model->getKey(),
        'key' => 'nullable',
        'value' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $read = $driver->getWithPresence($model, 'nullable');

    expect($read->exists)->toBeTrue()
        ->and($read->value)->toBeNull();
});

it('returns true when preference exists', function (): void {
    $driver = new SharedTableDriver(resolve('db'));
    $model = SharedPreferencesModel::create();

    $driver->set($model, 'theme', 'dark');

    expect($driver->has($model, 'theme'))->toBeTrue();
});

it('returns true when preference is absent', function (): void {
    $driver = new SharedTableDriver(resolve('db'));
    $model = SharedPreferencesModel::create();

    expect($driver->missing($model, 'theme'))->toBeTrue();
});

it('stores a preference value', function (): void {
    $driver = new SharedTableDriver(resolve('db'));
    $model = SharedPreferencesModel::create();

    $driver->set($model, 'theme', 'dark');

    expect($driver->get($model, 'theme'))->toBe('dark');
});

it('stores multiple preference values', function (): void {
    $driver = new SharedTableDriver(resolve('db'));
    $model = SharedPreferencesModel::create();

    $driver->setMultiple($model, [
        'notifications' => true,
        'theme' => 'dark',
    ]);

    expect($driver->all($model))->toBe(['notifications' => true, 'theme' => 'dark']);
});

it('stores multiple preference values in a single query', function (): void {
    $driver = new SharedTableDriver(resolve('db'));
    $model = SharedPreferencesModel::create();

    expectsDatabaseQueryCount(1);

    $driver->setMultiple($model, [
        'notifications' => true,
        'theme' => 'dark',
    ]);
});
