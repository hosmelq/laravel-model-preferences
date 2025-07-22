<?php

declare(strict_types=1);

arch()->preset()->php();
arch()->preset()->laravel();
arch()->preset()->security();

arch('annotations')
    ->expect('HosmelQ\ModelPreferences')
    ->toHaveMethodsDocumented()
    ->toHavePropertiesDocumented();

arch('strict types')
    ->expect('HosmelQ\ModelPreferences')
    ->toUseStrictTypes();
