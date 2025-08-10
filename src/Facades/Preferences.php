<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Facades;

use Closure;
use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\PendingPreferenceInteraction;
use HosmelQ\ModelPreferences\PreferencesManager;
use HosmelQ\ModelPreferences\PreferencesStore;
use Illuminate\Support\Facades\Facade;

/**
 * @method static PreferencesStore driver(null|string $driver = null)
 * @method static PreferencesManager extend(string $driver, Closure $callback)
 * @method static PendingPreferenceInteraction for(HasPreferences $model)
 */
class Preferences extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return PreferencesManager::class;
    }
}
