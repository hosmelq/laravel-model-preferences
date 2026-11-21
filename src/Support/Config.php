<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Support;

use Illuminate\Support\Facades\Config as ConfigFacade;
use InvalidArgumentException;

class Config
{
    public static function default(): string
    {
        return ConfigFacade::string('model-preferences.default');
    }

    public static function storesColumnName(): string
    {
        return ConfigFacade::string('model-preferences.stores.column.name');
    }

    public static function storesSharedConnection(): null|string
    {
        $value = ConfigFacade::get('model-preferences.stores.shared.connection');

        if ($value !== null && ! is_string($value)) {
            throw new InvalidArgumentException(sprintf(
                'Configuration value for key [%s] must be a string or null, %s given.',
                'model-preferences.stores.shared.connection',
                gettype($value),
            ));
        }

        return $value;
    }

    public static function storesSharedTable(): string
    {
        return ConfigFacade::string('model-preferences.stores.shared.table');
    }
}
