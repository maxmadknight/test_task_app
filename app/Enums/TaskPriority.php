<?php

declare(strict_types=1);

namespace App\Enums;

enum TaskPriority: int
{
    case LOW = 1;
    case STANDARD = 2;
    case MEDIUM = 3;
    case HIGH = 4;
    case URGENT = 5;
}
