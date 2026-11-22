<?php

declare(strict_types=1);

use HosmelQ\PhpCodingStandard\CodingStandard;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withCache('./.cache/ecs')
    ->withPaths([
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/src',
        __DIR__.'/tests',
        __DIR__.'/workbench',
    ])
    ->withRootFiles()
    ->withSets([CodingStandard::ECS_LARAVEL_PACKAGE]);
