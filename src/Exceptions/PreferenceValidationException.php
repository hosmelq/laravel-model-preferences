<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Exceptions;

use Exception;
use Illuminate\Contracts\Validation\Validator;

class PreferenceValidationException extends Exception
{
    /**
     * Create a new preference validation exception instance.
     */
    public function __construct(public Validator $validator)
    {
        parent::__construct('The given preference data was invalid.');
    }

    /**
     * Get all the validation error messages.
     *
     * @return array<string, array<string>>
     */
    public function errors(): array
    {
        return $this->validator->errors()->messages();
    }
}
