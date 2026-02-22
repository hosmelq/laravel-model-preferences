<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Models\Concerns;

use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Enums\StoreDriver;
use HosmelQ\ModelPreferences\Facades\Preferences;
use HosmelQ\ModelPreferences\PendingPreferenceInteraction;
use HosmelQ\ModelPreferences\Support\PreferencesConfig as ModelPreferencesConfig;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
trait InteractsWithPreferences
{
    /**
     * Get the preferences scoped interaction.
     *
     * @return PendingPreferenceInteraction<static>
     */
    public function preferences(): PendingPreferenceInteraction
    {
        return Preferences::driver($this->preferencesConfig()->getDriver())->for($this);
    }

    /**
     * Build the preferences configuration for the model.
     */
    public function preferencesConfig(): ModelPreferencesConfig
    {
        return ModelPreferencesConfig::configure();
    }

    /**
     * Boot the trait.
     */
    protected static function bootInteractsWithPreferences(): void
    {
        static::deleted(function (HasPreferences $model): void {
            if (in_array(
                $model->preferencesConfig()->getDriver(),
                [StoreDriver::Shared->value, StoreDriver::Table->value],
                true
            )) {
                $model->preferences()->clear();
            }
        });
    }

    /**
     * Initialize the trait.
     */
    protected function initializeInteractsWithPreferences(): void
    {
        if ($this->preferencesConfig()->getDriver() === StoreDriver::Column->value) {
            $column = $this->preferencesConfig()->getColumn();

            if (is_string($column) && $column !== '') {
                $this->mergeCasts([
                    $column => 'json',
                ]);
            }
        }
    }
}
