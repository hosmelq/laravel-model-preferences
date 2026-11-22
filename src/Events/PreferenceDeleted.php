<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Events;

use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Database\Eloquent\Model;

class PreferenceDeleted implements ShouldDispatchAfterCommit
{
    /**
     * @param list<string> $keys
     */
    public function __construct(public HasPreferences&Model $model, public array $keys) {}
}
