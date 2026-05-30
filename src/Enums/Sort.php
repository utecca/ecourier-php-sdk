<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Enums;

enum Sort: string
{
    case CreatedAt = 'created_at';
    case CreatedAtDesc = '-created_at';
}
