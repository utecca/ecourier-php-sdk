<?php

declare(strict_types=1);

namespace Ecourier\Enums;

enum DocumentType: string
{
    case ApplicationResponse = 'ApplicationResponse';
    case CreditNote = 'CreditNote';
    case Invoice = 'Invoice';
    case EndUserStatisticsReport = 'EndUserStatisticsReport';
    case TransactionStatisticsReport = 'TransactionStatisticsReport';
    case Other = 'Other';
}
