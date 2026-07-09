<?php

declare(strict_types=1);

namespace Ecourier\Requests\Companies;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteCompanyRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        private readonly string $company,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/companies/{$this->company}";
    }
}
