<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses;

use DateTimeImmutable;
use Ecourier\Data\Responses\Concerns\ParsesResponseData;
use Ecourier\Enums\Channel;
use Ecourier\Enums\Direction;
use Ecourier\Enums\DocumentStatus;
use Ecourier\Enums\DocumentType;

final readonly class DocumentListItemData
{
    use ParsesResponseData;

    /**
     * @param string $webUrl Link to the document in the eCourier portal.
     */
    public function __construct(
        public string $id,
        public ?DocumentStatus $status,
        public Channel $channel,
        public Direction $direction,
        public ?DocumentType $type,
        public DateTimeImmutable $createdAt,
        public ?DocumentCompanyData $company,
        public string $webUrl,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: self::required($data, 'id'),
            status: self::enum(DocumentStatus::class, self::optional($data, 'status')),
            channel: self::requiredEnum($data, 'channel', Channel::class),
            direction: self::requiredEnum($data, 'direction', Direction::class),
            type: self::enum(DocumentType::class, self::optional($data, 'type')),
            createdAt: self::date($data, 'created_at'),
            company: isset($data['company']) ? DocumentCompanyData::fromArray($data['company']) : null,
            webUrl: self::required($data, 'web_url'),
        );
    }
}
