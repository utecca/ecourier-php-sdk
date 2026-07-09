<?php

declare(strict_types=1);

namespace Ecourier\Enums;

enum Channel: string
{
    case Peppol = 'Peppol';
    case NemHandel = 'NemHandel';
}
