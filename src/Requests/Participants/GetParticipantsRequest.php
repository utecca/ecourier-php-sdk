<?php

declare(strict_types=1);

namespace Ecourier\Requests\Participants;

use BackedEnum;
use Ecourier\Enums\Channel;
use Ecourier\Enums\IdentifierScheme;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\PaginationPlugin\Contracts\Paginatable;

class GetParticipantsRequest extends Request implements Paginatable
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string|array|null $companyId = null,
        private readonly IdentifierScheme|array|null $scheme = null,
        private readonly Channel|array|null $channel = null,
        private readonly int $perPage = 10,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/participants';
    }

    protected function defaultQuery(): array
    {
        $query = ['per_page' => $this->perPage];

        $filter = array_filter([
            'company_id' => $this->values($this->companyId),
            'scheme' => $this->values($this->scheme),
            'channel' => $this->values($this->channel),
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
