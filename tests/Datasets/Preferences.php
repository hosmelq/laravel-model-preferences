<?php

declare(strict_types=1);

use Workbench\App\Enums\UserPreference;

dataset('complex values', [
    'mixed array' => [
        'data', [
            'array' => [1, 2, 3],
            'boolean' => true,
            'null' => null,
            'number' => 42,
            'string' => 'value',
        ],
    ],
    'nested array' => [
        'settings',
        [
            'notifications' => ['email' => true, 'sms' => false],
            'ui' => ['size' => 'large', 'theme' => 'dark'],
        ],
    ],
]);

dataset('key variations', [
    'enum' => [UserPreference::Theme, 'light'],
    'string' => ['notifications', false],
]);
