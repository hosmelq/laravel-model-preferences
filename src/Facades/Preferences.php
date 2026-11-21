<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Facades;

use Closure;
use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Contracts\PreferenceDriver;
use HosmelQ\ModelPreferences\Drivers\ArrayDriver;
use HosmelQ\ModelPreferences\PreferenceRepository;
use HosmelQ\ModelPreferences\PreferencesManager;
use HosmelQ\ModelPreferences\Support\PreferencesConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;
use Override;

/**
 * @method static PreferencesConfig config(HasPreferences&Model $model)
 * @method static PreferenceDriver driverFor(PreferencesConfig $config)
 * @method static PreferencesManager extend(string $driver, Closure $callback)
 * @method static ArrayDriver fake()
 * @method static PreferenceRepository for(HasPreferences&Model $model)
 * @method static void load(iterable<Model> $models)
 *
 * @see PreferencesManager
 */
class Preferences extends Facade
{
    #[Override]
    protected static function getFacadeAccessor(): string
    {
        return PreferencesManager::class;
    }
}
