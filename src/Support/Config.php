<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Support;

use Illuminate\Support\Facades\Config as ConfigFacade;

class Config
{
    /**
     * Get the default preference store name.
     */
    public static function default(): string
    {
        return ConfigFacade::string('model-preferences.default');
    }

    /**
     * Get the column driver name.
     */
    public static function storesColumnName(): string
    {
        return ConfigFacade::string('model-preferences.stores.column.name');
    }

    /**
     * Get the shared driver connection name.
     */
    public static function storesSharedConnection(): null|string
    {
        $value = ConfigFacade::get('model-preferences.stores.shared.connection');

        assert(
            is_null($value) || is_string($value),
            sprintf(
                'Configuration value for key [%s] must be a string or null, %s given.',
                'model-preferences.stores.shared.connection',
                gettype($value)
            )
        );

        return $value;
    }

    /**
     * Get the shared table name.
     */
    public static function storesSharedTable(): string
    {
        return ConfigFacade::string('model-preferences.stores.shared.table');
    }

    /**
     * Get the table driver connection name.
     */
    public static function storesTableConnection(): null|string
    {
        $value = ConfigFacade::get('model-preferences.stores.table.connection');

        assert(
            is_null($value) || is_string($value),
            sprintf(
                'Configuration value for key [%s] must be a string or null, %s given.',
                'model-preferences.stores.table.connection',
                gettype($value)
            )
        );

        return $value;
    }
}
