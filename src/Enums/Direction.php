<?php

declare(strict_types=1);

namespace Ecourier\Enums;

enum Direction: string
{
    case Send = 'Send';
    case Receive = 'Receive';
}
