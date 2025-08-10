<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Tests\TestSupport\Models;

use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Models\Concerns\InteractsWithPreferences;
use HosmelQ\ModelPreferences\Support\PreferencesConfig;
use Illuminate\Database\Eloquent\Model;

class SharedPreferencesModel extends Model implements HasPreferences
{
    use InteractsWithPreferences;

    protected $guarded = [];

    public function preferencesConfig(): PreferencesConfig
    {
        return PreferencesConfig::configure()
            ->withDriver('shared');
    }
}
