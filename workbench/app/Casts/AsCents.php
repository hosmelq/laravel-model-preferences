<?php

declare(strict_types=1);

namespace Workbench\App\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;

class AsCents implements Castable
{
    /**
     * @param array<array-key, mixed> $arguments
     *
     * @return class-string<Cents>
     */
    public static function castUsing(array $arguments): string
    {
        return Cents::class;
    }
}
