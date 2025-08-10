<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Support;

readonly class PreferenceRead
{
    /**
     * Create a new preference read result.
     */
    public function __construct(public bool $exists, public mixed $value)
    {
    }
}
