<?php

declare(strict_types=1);

use HosmelQ\ModelPreferences\Models\Preference;
use Workbench\App\Models\User;

it('has polymorphic relationship to preferable model', function (): void {
    $user = User::create();

    $user->setPreference('theme', 'light');

    $preference = Preference::first();

    expect($preference->preferable->is($user))->toBeTrue();
});
