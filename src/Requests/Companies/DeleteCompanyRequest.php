<?php

declare(strict_types=1);

namespace Ecourier\Requests\Companies;

use Ecourier\Requests\Concerns\EncodesPathParameters;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteCompanyRequest extends Request
{
    use EncodesPathParameters;

    protected Method $method = Method::DELETE;

    public function __construct(
        private readonly string $company,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/companies/' . self::pathParameter($this->company, 'company ID');
    }
}
