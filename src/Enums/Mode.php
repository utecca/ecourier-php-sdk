<?php

declare(strict_types=1);

namespace Ecourier\Enums;

enum Mode: string
{
    case Live = 'Live';
    case Test = 'Test';
}
