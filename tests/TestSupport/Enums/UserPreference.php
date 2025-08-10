<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Tests\TestSupport\Enums;

enum UserPreference: string
{
    case Notifications = 'notifications';
    case Theme = 'theme';
}
