<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Enums;

enum DocumentStatus: string
{
    case Pending = 'Pending';
    case Ready = 'Ready';
    case Delivered = 'Delivered';
    case Failed = 'Failed';
}
