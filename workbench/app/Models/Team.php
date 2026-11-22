<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Models\Concerns\InteractsWithPreferences;
use HosmelQ\ModelPreferences\Support\PreferencesConfig;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Team extends Model implements HasPreferences
{
    use HasUuids;
    use InteractsWithPreferences;

    public function preferencesConfig(): PreferencesConfig
    {
        return PreferencesConfig::configure()->withDriver('table');
    }
}
