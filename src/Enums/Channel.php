<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Enums;

enum Channel: string
{
    case Peppol = 'Peppol';
    case NemHandel = 'NemHandel';
}
