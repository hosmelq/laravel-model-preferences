<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Models\Concerns\InteractsWithPreferences;
use HosmelQ\ModelPreferences\Support\PreferencesConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Workbench\App\Enums\Theme;

class User extends Model implements HasPreferences
{
    use InteractsWithPreferences;

    protected $table = 'users';

    public function preferencesConfig(): PreferencesConfig
    {
        return PreferencesConfig::configure()
            ->withDriver('column')
            ->withCasts([
                'snoozed_until' => 'immutable_datetime',
                'theme' => Theme::class,
            ])
            ->withDefaults([
                'digest' => 'weekly',
                'theme' => Theme::System,
            ])
            ->withRules([
                'digest' => ['in:daily,weekly,never'],
                'notifications.email' => ['boolean'],
                'theme' => [Rule::enum(Theme::class)],
            ]);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['preferences' => 'array'];
    }
}
