<?php

declare(strict_types=1);

namespace Workbench\App\Models;

class ArchivedTeam extends Team
{
    protected $connection = 'archive';
}
