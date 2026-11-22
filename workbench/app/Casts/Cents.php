<?php

declare(strict_types=1);

namespace Workbench\App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsInboundAttributes;
use Illuminate\Database\Eloquent\Model;

class Cents implements CastsInboundAttributes
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return is_numeric($value) ? (int) round($value * 100) : $value;
    }
}
