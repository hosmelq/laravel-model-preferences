<?php

declare(strict_types=1);

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

    'default' => env('MODEL_PREFERENCES_DRIVER', 'shared'),

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
            'driver' => 'column',
            'name' => env('MODEL_PREFERENCES_COLUMN_NAME', 'preferences'),
        ],

        'table' => [
            'connection' => env('MODEL_PREFERENCES_DB_CONNECTION', env('DB_CONNECTION')),
            'driver' => 'table',
        ],

        'shared' => [
            'connection' => env('MODEL_PREFERENCES_DB_CONNECTION', env('DB_CONNECTION')),
            'driver' => 'shared',
            'table' => env('MODEL_PREFERENCES_TABLE', 'preferences'),
        ],

    ],

];
