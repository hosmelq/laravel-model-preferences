<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Contracts;

use HosmelQ\ModelPreferences\Models\Preference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @template TDeclaringModel of Model
 */
interface HasPreferences
{
    /**
     * Get the preferences for the model.
     *
     * @return MorphMany<Preference, TDeclaringModel>
     */
    public function preferences(): MorphMany;
}
