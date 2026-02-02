<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Tests\TestSupport\Models;

use HosmelQ\ModelPreferences\Support\PreferencesConfig as ModelPreferencesConfig;

class UserWithTablePreferences extends User
{
    public function preferencesConfig(): ModelPreferencesConfig
    {
        return ModelPreferencesConfig::configure()
            ->withDefaults(['theme' => 'system'])
            ->withDriver('table')
            ->withTable('users_preferences');
    }
}
