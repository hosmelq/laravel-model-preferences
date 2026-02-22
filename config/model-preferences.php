<?php

declare(strict_types=1);

use HosmelQ\ModelPreferences\Enums\StoreDriver;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Preference Store
    |--------------------------------------------------------------------------
    |
    | This option controls the default preference store that will be used.
    | This connection is utilized if no other store is explicitly specified
    | when running a preference operation within the application. Models
    | may override this default by implementing preferencesConfig().
    |
    | Supported: "column", "shared", "table"
    |
    */

    'default' => env('MODEL_PREFERENCES_DRIVER', StoreDriver::Shared->value),

    /*
    |--------------------------------------------------------------------------
    | Preference Stores
    |--------------------------------------------------------------------------
    |
    | Here, you can define all the preference stores for your application
    | along with their respective drivers.
    |
    */

    'stores' => [

        'column' => [
            'driver' => StoreDriver::Column->value,
            'name' => env('MODEL_PREFERENCES_COLUMN_NAME', 'preferences'),
        ],

        'shared' => [
            'connection' => env('MODEL_PREFERENCES_DB_CONNECTION', env('DB_CONNECTION')),
            'driver' => StoreDriver::Shared->value,
            'table' => env('MODEL_PREFERENCES_TABLE', 'preferences'),
        ],

        'table' => [
            'connection' => env('MODEL_PREFERENCES_DB_CONNECTION', env('DB_CONNECTION')),
            'driver' => StoreDriver::Table->value,
        ],

    ],

];
