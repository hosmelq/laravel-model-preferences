<?php

declare(strict_types=1);

use HosmelQ\ModelPreferences\Repositories\PreferenceRepository;
use Workbench\App\Models\User;

it('returns all preferences', function (): void {
    $repository = new PreferenceRepository();
    $user = User::create();

    $repository->set($user, 'language', 'en');
    $repository->set($user, 'notifications', true);
    $repository->set($user, 'theme', 'light');

    expect($repository->all($user))->toEqual([
        'language' => 'en',
        'notifications' => true,
        'theme' => 'light',
    ]);
});

it('returns empty array when no preferences exist', function (): void {
    $repository = new PreferenceRepository();
    $user = User::create();

    expect($repository->all($user))->toEqual([]);
});

it('deletes existing preference', function (): void {
    $repository = new PreferenceRepository();
    $user = User::create();

    $repository->set($user, 'theme', 'light');

    expect($repository->has($user, 'theme'))->toBeTrue();

    $repository->delete($user, 'theme');

    expect($repository->has($user, 'theme'))->toBeFalse();
});

it('deletes multiple preferences', function (): void {
    $repository = new PreferenceRepository();
    $user = User::create();

    $repository->set($user, 'language', 'en');
    $repository->set($user, 'notifications', true);
    $repository->set($user, 'theme', 'light');

    $repository->deleteMany($user, ['language', 'theme']);

    expect($repository->has($user, 'language'))->toBeFalse()
        ->and($repository->has($user, 'notifications'))->toBeTrue()
        ->and($repository->has($user, 'theme'))->toBeFalse();
});

it('returns existing preference value', function (): void {
    $repository = new PreferenceRepository();
    $user = User::create();

    $repository->set($user, 'theme', 'light');

    expect($repository->get($user, 'theme'))->toBe('light');
});

it('returns null for nonexistent key', function (): void {
    $repository = new PreferenceRepository();
    $user = User::create();

    expect($repository->get($user, 'nonexistent'))->toBeNull();
});

it('returns complex data types correctly', function (): void {
    $repository = new PreferenceRepository();
    $user = User::create();

    $data = [
        'array' => [1, 2, 3],
        'boolean' => true,
        'null' => null,
        'number' => 42,
        'object' => ['key' => 'value'],
        'string' => 'test',
    ];

    $repository->set($user, 'complex', $data);

    expect($repository->get($user, 'complex'))->toEqual($data);
});

it('returns true when key exists', function (): void {
    $repository = new PreferenceRepository();
    $user = User::create();

    $repository->set($user, 'theme', 'light');

    expect($repository->has($user, 'theme'))->toBeTrue();
});

it('returns false when key does not exist', function (): void {
    $repository = new PreferenceRepository();
    $user = User::create();

    expect($repository->has($user, 'nonexistent'))->toBeFalse();
});

it('creates new preference', function (): void {
    $repository = new PreferenceRepository();
    $user = User::create();

    $repository->set($user, 'theme', 'light');

    expect($repository->get($user, 'theme'))->toBe('light')
        ->and($user->preferences()->get())->toHaveCount(1);
});

it('updates existing preference', function (): void {
    $repository = new PreferenceRepository();
    $user = User::create();

    $repository->set($user, 'theme', 'light');

    expect($repository->get($user, 'theme'))->toBe('light');

    $repository->set($user, 'theme', 'light');

    expect($repository->get($user, 'theme'))->toBe('light')
        ->and($user->preferences()->get())->toHaveCount(1);
});

it('sets multiple preferences', function (): void {
    $repository = new PreferenceRepository();
    $user = User::create();

    $preferences = [
        'language' => 'en',
        'notifications' => true,
        'theme' => 'light',
    ];

    $repository->setMany($user, $preferences);

    expect($repository->all($user))->toEqual($preferences);
});
