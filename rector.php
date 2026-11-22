<?php

declare(strict_types=1);

use HosmelQ\PhpCodingStandard\CodingStandard;
use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withCache('./.cache/rector', FileCacheStorage::class)
    ->withPaths([
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/src',
        __DIR__.'/tests',
        __DIR__.'/workbench',
    ])
    ->withSets([CodingStandard::RECTOR_LARAVEL_PACKAGE, CodingStandard::RECTOR_PEST]);
