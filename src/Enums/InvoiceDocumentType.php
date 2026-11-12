<?php

declare(strict_types=1);

namespace Ecourier\Enums;

/**
 * The document types that can be sent as JSON.
 */
enum InvoiceDocumentType: string
{
    case Invoice = 'Invoice';
    case CreditNote = 'CreditNote';
}
