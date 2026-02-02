<?php

declare(strict_types=1);

use HosmelQ\ModelPreferences\Facades\Preferences;
use HosmelQ\ModelPreferences\Tests\TestSupport\Models\PreferencesModelWithoutTrait;
use HosmelQ\ModelPreferences\Tests\TestSupport\Models\User;

it('rejects mismatched model drivers', function (): void {
    config(['model-preferences.default' => 'shared']);

    $user = User::create();

    Preferences::driver('table')->for($user);
})->throws(InvalidArgumentException::class, 'The model preference driver does not match the selected driver.');

it('requires the interacts with preferences trait', function (): void {
    $model = new PreferencesModelWithoutTrait();

    Preferences::for($model);
})->throws(InvalidArgumentException::class, 'The model must use the InteractsWithPreferences trait.');

it('returns null for missing preferences', function (): void {
    $user = User::create();

    expect(Preferences::for($user)->get('missing'))->toBeNull();
});
