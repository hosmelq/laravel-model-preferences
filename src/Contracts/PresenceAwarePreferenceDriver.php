<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Contracts;

use HosmelQ\ModelPreferences\Support\PreferenceRead;

interface PresenceAwarePreferenceDriver extends PreferenceDriver
{
    /**
     * Get a preference value for a model with presence information.
     */
    public function getWithPresence(HasPreferences $model, string $key): PreferenceRead;
}
