<?php

declare(strict_types=1);

namespace Workbench\App\Enums;

enum Theme: string
{
    case Dark = 'dark';
    case Light = 'light';
    case System = 'system';
}
