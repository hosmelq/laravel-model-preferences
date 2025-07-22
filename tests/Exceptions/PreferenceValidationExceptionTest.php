<?php

declare(strict_types=1);

use HosmelQ\ModelPreferences\Exceptions\PreferenceValidationException;
use Illuminate\Support\Facades\Validator;

it('provides standard message and validation errors', function (): void {
    $validator = Validator::make(['theme' => 'invalid'], ['theme' => 'in:light,dark']);

    $validator->fails();

    $exception = new PreferenceValidationException($validator);

    expect($exception)
        ->getMessage()->toBe('The given preference data was invalid.')
        ->errors()->toBe($validator->errors()->messages());
});
