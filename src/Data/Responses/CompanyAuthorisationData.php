<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses;

use Ecourier\Data\Responses\Concerns\ParsesResponseData;

final readonly class CompanyAuthorisationData
{
    use ParsesResponseData;

    public function __construct(
        public bool $signed,
        public ?string $signUrl,
        public CompanyAuthorisationSignerData $signer,
        public ?string $redirectUrl,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            signed: (bool) self::required($data, 'signed'),
            signUrl: self::optional($data, 'sign_url'),
            signer: CompanyAuthorisationSignerData::fromArray(self::required($data, 'signer')),
            redirectUrl: self::optional($data, 'redirect_url'),
        );
    }
}
