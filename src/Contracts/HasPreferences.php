<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Contracts;

use HosmelQ\ModelPreferences\Support\PreferencesConfig;
use Illuminate\Database\Eloquent\Model;

/**
 * @phpstan-require-extends Model
 */
interface HasPreferences
{
    /**
     * Build the preferences configuration for the model.
     */
    public function preferencesConfig(): PreferencesConfig;
}
