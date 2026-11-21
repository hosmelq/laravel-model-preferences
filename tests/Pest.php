<?php

declare(strict_types=1);

use HosmelQ\ModelPreferences\Tests\TestCase;
use Workbench\App\Models\SharedUser;
use Workbench\App\Models\TableUser;
use Workbench\App\Models\User;

pest()->extend(TestCase::class)->in(__DIR__);

dataset('models', [
    'column' => User::class,
    'shared' => SharedUser::class,
    'table' => TableUser::class,
]);

dataset('database models', [
    'shared' => SharedUser::class,
    'table' => TableUser::class,
]);
