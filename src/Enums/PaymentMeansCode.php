<?php

declare(strict_types=1);

namespace Ecourier\Enums;

enum PaymentMeansCode: string
{
    case CreditTransfer = '30';
    case DebitTransfer = '31';
    case PaymentToAccount = '42';
}
