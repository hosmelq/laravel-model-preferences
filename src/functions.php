<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences;

use BackedEnum;
use UnitEnum;

if (! function_exists('HosmelQ\ModelPreferences\enum_value')) {
    /**
     * Return a scalar value for the given value that might be an enum.
     *
     * @internal
     *
     * @template TValue
     * @template TDefault
     *
     * @param TValue $value
     * @param callable(TValue): TDefault|TDefault $default
     *
     * @return ($value is empty ? TDefault : mixed)
     */
    function enum_value($value, mixed $default = null)
    {
        return match (true) {
            $value instanceof BackedEnum => $value->value,
            $value instanceof UnitEnum => $value->name,

            default => $value ?? value($default),
        };
    }
}
