<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Preference Store
    |--------------------------------------------------------------------------
    |
    | This option controls the store used by models that do not select one
    | with withDriver() in their preferencesConfig() method.
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
    | The column store keeps preferences in a JSON column of the model's table.
    | The shared store keeps every model's preferences in one polymorphic
    | table, on the given connection or the default one. The table store uses
    | the connection of each model.
    |
    */

    'stores' => [

        'column' => [
            'name' => env('MODEL_PREFERENCES_COLUMN_NAME', 'preferences'),
        ],

        'shared' => [
            'connection' => env('MODEL_PREFERENCES_DB_CONNECTION'),
            'table' => env('MODEL_PREFERENCES_TABLE', 'preferences'),
        ],

    ],

];
