<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Tests\TestSupport\Models;

use HosmelQ\ModelPreferences\Support\PreferencesConfig as ModelPreferencesConfig;

class UserWithInvalidTable extends User
{
    public function preferencesConfig(): ModelPreferencesConfig
    {
        return ModelPreferencesConfig::configure()
            ->withDriver('table')
            ->withTable('');
    }
}
