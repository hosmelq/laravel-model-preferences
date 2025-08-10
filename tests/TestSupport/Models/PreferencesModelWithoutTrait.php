<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Tests\TestSupport\Models;

use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Support\PreferencesConfig;
use Illuminate\Database\Eloquent\Model;

class PreferencesModelWithoutTrait extends Model implements HasPreferences
{
    protected $table = 'users';

    public function preferencesConfig(): PreferencesConfig
    {
        return PreferencesConfig::configure();
    }
}
