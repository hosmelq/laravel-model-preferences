<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Models\Concerns\InteractsWithPreferences;
use Illuminate\Database\Eloquent\Model;

class Team extends Model implements HasPreferences
{
    use InteractsWithPreferences;

    protected $guarded = [];

    public function preferenceDefaults(): array
    {
        return [
            'max_members' => 10,
            'visibility' => 'private',
        ];
    }
}
