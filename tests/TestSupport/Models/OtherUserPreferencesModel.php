<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Tests\TestSupport\Models;

use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Models\Concerns\InteractsWithPreferences;
use Illuminate\Database\Eloquent\Model;

class OtherUserPreferencesModel extends Model implements HasPreferences
{
    use InteractsWithPreferences;

    protected $guarded = [];

    protected $table = 'users';
}
