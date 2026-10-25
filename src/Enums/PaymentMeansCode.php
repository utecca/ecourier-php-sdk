<?php

declare(strict_types=1);

namespace Ecourier\Enums;

enum PaymentMeansCode: string
{
    /** Credit transfer to an IBAN. */
    case CreditTransfer = '30';

    /** Bank transfer to an IBAN, with the BIC as bank ID. */
    case DebitTransfer = '31';

    /** Bank transfer to a domestic account (DK:BBAN), with the 4-digit registration number as bank ID. */
    case PaymentToAccount = '42';

    /** @return AccountSchemeId[] */
    public function accountSchemes(): array
    {
        return match ($this) {
            self::CreditTransfer, self::DebitTransfer => [AccountSchemeId::IBAN],
            self::PaymentToAccount => [AccountSchemeId::DK_BBAN],
        };
    }
}
