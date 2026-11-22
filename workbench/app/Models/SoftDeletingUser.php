<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class SoftDeletingUser extends SharedUser
{
    use SoftDeletes;
}
