<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences;

use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Drivers\ColumnDriver;
use HosmelQ\ModelPreferences\Drivers\SharedTableDriver;
use HosmelQ\ModelPreferences\Drivers\TableDriver;
use HosmelQ\ModelPreferences\Enums\StoreDriver;
use HosmelQ\ModelPreferences\Support\Config as PreferencesConfig;
use Illuminate\Support\Manager;

class PreferencesManager extends Manager
{
    /**
     * Create a scoped instance for a model using the default store.
     */
    public function for(HasPreferences $model): PendingPreferenceInteraction
    {
        /** @var PreferencesStore $store */
        $store = $this->driver();

        return $store->for($model);
    }

    /**
     * Get the default store name.
     */
    public function getDefaultDriver(): string
    {
        return PreferencesConfig::default();
    }

    /**
     * Create the column driver.
     */
    protected function createColumnDriver(): PreferencesStore
    {
        return new PreferencesStore(StoreDriver::Column->value, new ColumnDriver());
    }

    /**
     * Create the shared table driver.
     */
    protected function createSharedDriver(): PreferencesStore
    {
        return new PreferencesStore(StoreDriver::Shared->value, new SharedTableDriver(
            $this->container->make('db')
        ));
    }

    /**
     * Create the table driver.
     */
    protected function createTableDriver(): PreferencesStore
    {
        return new PreferencesStore(StoreDriver::Table->value, new TableDriver(
            $this->container->make('db')
        ));
    }
}
