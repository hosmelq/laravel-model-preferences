<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Models\Concerns\InteractsWithPreferences;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class User extends Model implements HasPreferences
{
    use InteractsWithPreferences;

    protected $guarded = [];

    public function preferenceDefaults(): array
    {
        return [
            'notifications' => true,
            'theme' => 'system',
        ];
    }

    public function preferenceRules(): array
    {
        return [
            'notifications' => ['boolean'],
            'theme' => [Rule::in(['dark', 'light', 'system'])],
        ];
    }
}
