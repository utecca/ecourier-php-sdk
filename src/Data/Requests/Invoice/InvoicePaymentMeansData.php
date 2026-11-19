<?php

declare(strict_types=1);

namespace Ecourier\Data\Requests\Invoice;

use Ecourier\Enums\AccountSchemeId;
use Ecourier\Enums\PaymentMeansCode;
use InvalidArgumentException;

/**
 * The account's scheme and whether a bank ID is required depend on the payment means code —
 * see {@see PaymentMeansCode::accountSchemes()} and {@see PaymentMeansCode::requiresBankId()}.
 */
final readonly class InvoicePaymentMeansData
{
    public function __construct(
        public PaymentMeansCode $code,
        public InvoicePaymentAccountData $account,
        public ?int $id = null,
        public ?string $remittanceText = null,
    ) {
        if (! in_array($account->scheme, $code->accountSchemes(), true)) {
            $schemes = implode(' or ', array_map(fn(AccountSchemeId $scheme) => $scheme->value, $code->accountSchemes()));

            throw new InvalidArgumentException("Payment means code {$code->value} requires the {$schemes} account scheme.");
        }

        if ($code->requiresBankId() && ($account->bankId === null || trim($account->bankId) === '')) {
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
