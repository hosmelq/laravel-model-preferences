<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Enums;

enum StoreDriver: string
{
    case Column = 'column';
    case Shared = 'shared';
    case Table = 'table';
}
