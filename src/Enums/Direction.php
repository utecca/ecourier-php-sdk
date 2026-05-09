<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Enums;

enum Direction: string
{
    case Send = 'Send';
    case Receive = 'Receive';
}
