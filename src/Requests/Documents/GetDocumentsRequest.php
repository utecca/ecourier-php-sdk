<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Requests\Documents;

use DateTimeImmutable;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\PaginationPlugin\Contracts\Paginatable;

class GetDocumentsRequest extends Request implements Paginatable
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly ?string $status = null,
        private readonly ?string $direction = null,
        private readonly ?DateTimeImmutable $from = null,
        private readonly ?DateTimeImmutable $to = null,
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
            'from' => $this->from?->format('Y-m-d'),
            'to' => $this->to?->format('Y-m-d'),
            'per_page' => $this->perPage,
        ], fn($value) => $value !== null);
    }
}
