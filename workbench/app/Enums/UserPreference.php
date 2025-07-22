<?php

declare(strict_types=1);

namespace Workbench\App\Enums;

enum UserPreference: string
{
    case Notifications = 'notifications';
    case Theme = 'theme';
}
