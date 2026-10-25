<?php

declare(strict_types=1);

namespace Ecourier\Data\Invoice;

use Ecourier\Enums\AccountSchemeId;
use Ecourier\Enums\PaymentMeansCode;
use InvalidArgumentException;

/**
 * Codes 30 and 31 require an IBAN account, and code 42 a DK:BBAN account. Code 31 and DK:BBAN accounts
 * also require a bank ID (the BIC for IBAN, the 4-digit registration number for DK:BBAN).
 */
class InvoicePaymentMeansData
{
    public function __construct(
        public readonly PaymentMeansCode $code,
        public readonly InvoicePaymentAccountData $account,
        public readonly ?int $id = null,
        public readonly ?string $remittanceText = null,
    ) {
        if (! in_array($account->scheme, $code->accountSchemes(), true)) {
            $schemes = implode(' or ', array_map(fn(AccountSchemeId $scheme) => $scheme->value, $code->accountSchemes()));

            throw new InvalidArgumentException("Payment means code {$code->value} requires the {$schemes} account scheme.");
        }

        $bankIdRequired = $code === PaymentMeansCode::DebitTransfer || $account->scheme === AccountSchemeId::DK_BBAN;

        if ($bankIdRequired && ($account->bankId === null || trim($account->bankId) === '')) {
            throw new InvalidArgumentException("Payment means code {$code->value} with a {$account->scheme->value} account requires a bank ID.");
        }
    }

    public function toArray(): array
    {
        $data = ['code' => $this->code->value];

        if ($this->id !== null) {
            $data['id'] = $this->id;
        }

        if ($this->remittanceText !== null) {
            $data['remittance_text'] = $this->remittanceText;
        }

        $data['account'] = $this->account->toArray();

        return $data;
    }
}
