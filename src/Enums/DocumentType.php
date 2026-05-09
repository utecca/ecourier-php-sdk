<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Enums;

enum DocumentType: string
{
    case ApplicationResponse = 'ApplicationResponse';
    case CreditNote = 'CreditNote';
    case Invoice = 'Invoice';
    case Other = 'Other';
}
