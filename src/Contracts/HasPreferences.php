<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Contracts;

use HosmelQ\ModelPreferences\PreferenceRepository;
use HosmelQ\ModelPreferences\Support\PreferencesConfig;
use Illuminate\Database\Eloquent\Model;

/**
 * @phpstan-require-extends Model
 */
interface HasPreferences
{
    public function preferences(): PreferenceRepository;

    public function preferencesConfig(): PreferencesConfig;
}
