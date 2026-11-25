<?php

declare(strict_types=1);

namespace Ecourier\Enums;

enum DocumentType: string
{
    case ApplicationResponse = 'ApplicationResponse';
    case CreditNote = 'CreditNote';
    case Invoice = 'Invoice';
    case Order = 'Order';
    case OrderCancellation = 'OrderCancellation';
    case OrderChange = 'OrderChange';
    case OrderResponse = 'OrderResponse';
    case OrderResponseSimple = 'OrderResponseSimple';
    case Reminder = 'Reminder';
    case EndUserStatisticsReport = 'EndUserStatisticsReport';
    case TransactionStatisticsReport = 'TransactionStatisticsReport';
    case Other = 'Other';
}
