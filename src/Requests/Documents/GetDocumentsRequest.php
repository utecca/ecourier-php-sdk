<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Requests\Documents;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\PaginationPlugin\Contracts\Paginatable;

class GetDocumentsRequest extends Request implements Paginatable
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly ?string $status = null,
        private readonly ?string $direction = null,
        private readonly ?string $from = null,
        private readonly ?string $to = null,
        private readonly int $perPage = 25,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/documents';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'status' => $this->status,
            'direction' => $this->direction,
            'from' => $this->from,
            'to' => $this->to,
            'per_page' => $this->perPage,
        ], fn($value) => $value !== null);
    }
}
