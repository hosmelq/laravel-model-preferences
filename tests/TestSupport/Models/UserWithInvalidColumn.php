<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Tests\TestSupport\Models;

use HosmelQ\ModelPreferences\Support\PreferencesConfig as ModelPreferencesConfig;

class UserWithInvalidColumn extends User
{
    public function preferencesConfig(): ModelPreferencesConfig
    {
        return ModelPreferencesConfig::configure()
            ->withColumn('');
    }
}
