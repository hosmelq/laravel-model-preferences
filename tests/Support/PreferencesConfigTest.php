<?php

declare(strict_types=1);

use HosmelQ\ModelPreferences\Enums\StoreDriver;
use HosmelQ\ModelPreferences\Support\PreferencesConfig;

it('builds a preferences configuration with fluent setters', function (): void {
    $config = PreferencesConfig::configure()
        ->withColumn('prefs')
        ->withDefaults(['theme' => 'dark'])
        ->withDriver('table')
        ->withRules(['theme' => ['string']])
        ->withTable('users_preferences');

    expect($config)
        ->getColumn()->toBe('prefs')
        ->getDefaults()->toBe(['theme' => 'dark'])
        ->getDriver()->toBe('table')
        ->getRules()->toBe(['theme' => ['string']])
        ->getTable()->toBe('users_preferences');
});

it('accepts a store driver enum', function (): void {
    $config = PreferencesConfig::configure()
        ->withDriver(StoreDriver::Table);

    expect($config->getDriver())->toBe(StoreDriver::Table->value);
});
