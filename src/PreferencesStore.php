<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences;

use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Contracts\PreferenceDriver;
use HosmelQ\ModelPreferences\Models\Concerns\InteractsWithPreferences;
use InvalidArgumentException;

class PreferencesStore
{
    /**
     * Create a new preferences store instance.
     */
    public function __construct(protected string $name, protected PreferenceDriver $driver)
    {
    }

    /**
     * Create a scoped instance for a model.
     */
    public function for(HasPreferences $model): PendingPreferenceInteraction
    {
        if (! in_array(InteractsWithPreferences::class, class_uses_recursive($model), true)) {
            throw new InvalidArgumentException('The model must use the InteractsWithPreferences trait.');
        }

        if ($model->preferencesConfig()->getDriver() !== $this->name) {
            throw new InvalidArgumentException('The model preference driver does not match the selected driver.');
        }

        return new PendingPreferenceInteraction($this->driver, $model);
    }
}
