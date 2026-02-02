<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences;

use BackedEnum;
use UnitEnum;

/**
 * Return a scalar value for the given value that might be an enum.
 *
 * @internal
 */
function enum_value(mixed $value, mixed $default = null): null|int|string
{
    $resolved = match (true) {
        $value instanceof BackedEnum => $value->value,
        $value instanceof UnitEnum => $value->name,
        default => $value ?? value($default),
    };

    return is_int($resolved) || is_string($resolved) ? $resolved : null;
}
