<?php

declare(strict_types=1);

namespace Ecourier\Data\Requests;

final readonly class UpdateCompanyData
{
    public function __construct(
        public string $name,
    ) {}
}
