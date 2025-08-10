<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Exceptions;

use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use InvalidArgumentException;

class PreferenceColumnNotFound extends InvalidArgumentException
{
    /**
     * Create a preference column not configured exception for a model.
     */
    public static function invalid(HasPreferences $model): self
    {
        return new self(sprintf(
            'The preferences column is not configured for the [%s] table.',
            $model->getTable()
        ));
    }
}
