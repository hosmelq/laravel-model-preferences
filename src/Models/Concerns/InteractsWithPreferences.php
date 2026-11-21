<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Models\Concerns;

use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Facades\Preferences;
use HosmelQ\ModelPreferences\PreferenceRepository;
use HosmelQ\ModelPreferences\Support\PreferencesConfig;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use UnitEnum;

/**
 * @phpstan-require-extends Model
 *
 * @phpstan-require-implements HasPreferences
 */
trait InteractsWithPreferences
{
    public function preferences(): PreferenceRepository
    {
        return Preferences::for($this);
    }

    public function preferencesConfig(): PreferencesConfig
    {
        return PreferencesConfig::configure();
    }

    /**
     * @param Builder<static> $query
     */
    public function scopeWherePreference(Builder $query, string|UnitEnum $key, mixed $value): void
    {
        $this->preferences()->whereEquals($query, $key, $value);
    }

    protected static function bootInteractsWithPreferences(): void
    {
        $event = in_array(SoftDeletes::class, class_uses_recursive(static::class), true)
            ? 'forceDeleted'
            : 'deleted';

        static::registerModelEvent($event, function (HasPreferences&Model $model): void {
            $config = Preferences::config($model);

            Preferences::driverFor($config)->clear($model, $config);
        });
    }
}
