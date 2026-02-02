<?php

declare(strict_types=1);

use HosmelQ\ModelPreferences\PendingPreferenceInteraction;
use HosmelQ\ModelPreferences\PreferencesManager;
use HosmelQ\ModelPreferences\PreferencesStore;
use HosmelQ\ModelPreferences\Tests\TestSupport\Drivers\DummyPreferenceDriver;
use HosmelQ\ModelPreferences\Tests\TestSupport\Models\User;
use HosmelQ\ModelPreferences\Tests\TestSupport\Models\UserWithCustomDriver;

it('returns a pending interaction', function (): void {
    $manager = resolve(PreferencesManager::class);
    $user = User::create();

    expect($manager->for($user))->toBeInstanceOf(PendingPreferenceInteraction::class);
});

it('throws when the default store is not a string', function (): void {
    $manager = resolve(PreferencesManager::class);

    config(['model-preferences.default' => ['invalid']]);

    $manager->getDefaultDriver();
})->throws(InvalidArgumentException::class, 'Configuration value for key [model-preferences.default] must be a string, array given.');

it('throws when requesting an unsupported driver', function (): void {
    $manager = resolve(PreferencesManager::class);

    $manager->driver('unknown');
})->throws(InvalidArgumentException::class, 'Driver [unknown] not supported.');

it('creates built-in drivers', function (): void {
    $manager = resolve(PreferencesManager::class);

    expect($manager)
        ->driver('column')->toBeInstanceOf(PreferencesStore::class)
        ->driver('shared')->toBeInstanceOf(PreferencesStore::class);
});

it('registers custom drivers', function (): void {
    $manager = resolve(PreferencesManager::class);

    $manager->extend('custom', fn (): PreferencesStore => new PreferencesStore('custom', new DummyPreferenceDriver()));

    $user = UserWithCustomDriver::create();

    expect($manager->driver('custom')->for($user)->all())->toBe([]);
});
