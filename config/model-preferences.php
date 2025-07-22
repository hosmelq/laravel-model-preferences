<?php

declare(strict_types=1);

use HosmelQ\ModelPreferences\Models\Preference;

return [

    /*
    |--------------------------------------------------------------------------
    | Preferences Table Name
    |--------------------------------------------------------------------------
    |
    | This is the name of the table that will be used to store preferences.
    | You can change this to any table name you prefer.
    |
    */

    'table' => env('MODEL_PREFERENCES_TABLE', 'preferences'),

    /*
    |--------------------------------------------------------------------------
    | Model Configuration
    |--------------------------------------------------------------------------
    |
    | Specify the model classes to use for preferences. You can extend
    | the default models to add custom functionality if needed.
    |
    */

    'models' => [

        'preference' => Preference::class,

    ],

];
