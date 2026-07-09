<?php

declare(strict_types=1);

namespace Ecourier\Requests\Companies;

use BackedEnum;
use Ecourier\Enums\Channel;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\PaginationPlugin\Contracts\Paginatable;

class GetCompaniesRequest extends Request implements Paginatable
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly Channel|array|null $channel = null,
        private readonly string|array|null $companyNo = null,
        private readonly string|array|null $country = null,
        private readonly string|array|null $name = null,
        private readonly ?bool $signed = null,
        private readonly int $perPage = 10,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/companies';
    }

    protected function defaultQuery(): array
    {
        $query = ['per_page' => $this->perPage];

        $filter = array_filter([
            'channel' => $this->values($this->channel),
            'company_no' => $this->values($this->companyNo),
            'country' => $this->values($this->country),
            'name' => $this->values($this->name),
            'signed' => $this->signed,
        ], fn($value) => $value !== null);

        if ($filter !== []) {
            $query['filter'] = $filter;
        }

        return $query;
    }

    private function values(BackedEnum|string|array|null $values): ?array
    {
        if ($values === null || $values === []) {
            return null;
        }

        return array_map(
            fn(BackedEnum|string $value) => $value instanceof BackedEnum ? $value->value : $value,
            is_array($values) ? $values : [$values],
        );
    }
}
