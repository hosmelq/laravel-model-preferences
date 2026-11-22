<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use HosmelQ\ModelPreferences\Support\PreferencesConfig;

class TableUser extends User
{
    public function preferencesConfig(): PreferencesConfig
    {
        return parent::preferencesConfig()->withDriver('table');
    }
}
