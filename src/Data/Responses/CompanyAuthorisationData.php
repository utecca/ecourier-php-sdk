<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses;

readonly class CompanyAuthorisationData
{
    public function __construct(
        public bool $signed,
        public ?string $signUrl,
        public CompanyAuthorisationSignerData $signer,
        public ?string $redirectUrl,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            signed: $data['signed'],
            signUrl: $data['sign_url'],
            signer: CompanyAuthorisationSignerData::fromArray($data['signer']),
            redirectUrl: $data['redirect_url'],
        );
    }

    public function toArray(): array
    {
        return [
            'signed' => $this->signed,
            'sign_url' => $this->signUrl,
            'signer' => $this->signer->toArray(),
            'redirect_url' => $this->redirectUrl,
        ];
    }
}
