<?php

declare(strict_types=1);

namespace Ecourier\Data;

class CompanyAuthorisationData
{
    public function __construct(
        public readonly bool $signed,
        public readonly ?string $signUrl,
        public readonly CompanyAuthorisationSignerData $signer,
        public readonly ?string $redirectUrl = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            signed: $data['signed'],
            signUrl: $data['sign_url'],
            signer: CompanyAuthorisationSignerData::fromArray($data['signer']),
            redirectUrl: $data['redirect_url'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'signed' => $this->signed,
            'sign_url' => $this->signUrl,
            'redirect_url' => $this->redirectUrl,
            'signer' => $this->signer->toArray(),
        ];
    }
}
