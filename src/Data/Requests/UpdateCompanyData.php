<?php

declare(strict_types=1);

namespace Ecourier\Data\Requests;

readonly class UpdateCompanyData
{
    public function __construct(
        public string $name,
    ) {}
}
