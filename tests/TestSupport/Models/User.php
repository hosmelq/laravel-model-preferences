<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Tests\TestSupport\Models;

use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Models\Concerns\InteractsWithPreferences;
use HosmelQ\ModelPreferences\Support\PreferencesConfig as ModelPreferencesConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class User extends Model implements HasPreferences
{
    use InteractsWithPreferences;

    protected $guarded = [];

    protected $table = 'users';

    public function preferencesConfig(): ModelPreferencesConfig
    {
        $config = ModelPreferencesConfig::configure()
            ->withDefaults([
                'notifications' => true,
                'theme' => 'system',
            ])
            ->withRules([
                'notifications' => ['boolean'],
                'theme' => [Rule::in(['dark', 'light', 'system'])],
            ]);

        if ($config->getDriver() === 'table') {
            $config->withTable('users_preferences');
        }

        return $config;
    }
}
