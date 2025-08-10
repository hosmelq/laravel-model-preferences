<?php

declare(strict_types=1);

use HosmelQ\ModelPreferences\PendingPreferenceInteraction;
use HosmelQ\ModelPreferences\Tests\TestSupport\Models\SharedPreferencesModel;
use HosmelQ\ModelPreferences\Tests\TestSupport\Models\User;
use Illuminate\Support\Facades\DB;

it('returns a pending interaction', function (): void {
    $user = User::create();

    expect($user->preferences())->toBeInstanceOf(PendingPreferenceInteraction::class);
});

it('reads the default store from config', function (): void {
    $user = new User();

    expect($user->preferencesConfig()->getDriver())->toBe('shared');
});

it('clears shared preferences on delete', function (): void {
    $model = SharedPreferencesModel::create();

    $model->preferences()->set('theme', 'dark');

    $existsBefore = DB::table('preferences')
        ->where('preferable_type', $model->getMorphClass())
        ->where('preferable_id', $model->getKey())
        ->exists();

    expect($existsBefore)->toBeTrue();

    $model->delete();

    $existsAfter = DB::table('preferences')
        ->where('preferable_type', $model->getMorphClass())
        ->where('preferable_id', $model->getKey())
        ->exists();

    expect($existsAfter)->toBeFalse();
});

it('adds json casts for column preferences', function (): void {
    config(['model-preferences.default' => 'column']);

    $user = new User();

    expect($user->getCasts())
        ->toHaveKey('preferences')
        ->preferences->toBe('json');
});
