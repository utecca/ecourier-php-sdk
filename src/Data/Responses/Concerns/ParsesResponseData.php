<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses\Concerns;

use BackedEnum;
use DateTimeImmutable;
use Ecourier\Exceptions\MissingResponseFieldException;

/**
 * Reads API response fields with one set of rules for every response data object:
 *
 * - A field for a non-nullable property is required. If it is missing or null,
 *   a {@see MissingResponseFieldException} is thrown.
 * - A field for a nullable property is optional and becomes null when missing.
 * - A list field is optional and becomes an empty list when missing.
 *
 * @internal
 */
trait ParsesResponseData
{
    /**
     * @param array<array-key, mixed> $data
     */
    protected static function required(array $data, string $key): mixed
    {
        if (! isset($data[$key])) {
            throw new MissingResponseFieldException(static::class, $key);
        }

        return $data[$key];
    }

    /**
     * @param array<array-key, mixed> $data
     */
    protected static function optional(array $data, string $key): mixed
    {
        return $data[$key] ?? null;
    }

    /**
     * @param array<array-key, mixed> $data
     * @return list<mixed>
     */
    protected static function list(array $data, string $key): array
    {
        $value = $data[$key] ?? [];

        return is_array($value) ? array_values($value) : [];
    }

    /**
     * @template TEnum of BackedEnum
     * @param class-string<TEnum> $enum
     * @return TEnum|null
     */
    protected static function enum(string $enum, mixed $value): ?BackedEnum
    {
        return is_string($value) || is_int($value) ? $enum::tryFrom($value) : null;
    }

    /**
     * @template TEnum of BackedEnum
     * @param array<array-key, mixed> $data
     * @param class-string<TEnum> $enum
     * @return TEnum
     */
    protected static function requiredEnum(array $data, string $key, string $enum): BackedEnum
    {
        return $enum::from(self::required($data, $key));
    }

    /**
     * @template TEnum of BackedEnum
     * @param array<array-key, mixed> $data
     * @param class-string<TEnum> $enum
     * @return list<TEnum>
     */
    protected static function enumList(array $data, string $key, string $enum): array
    {
        return array_map(fn(mixed $value) => $enum::from($value), self::list($data, $key));
    }

    /**
     * @param array<array-key, mixed> $data
     */
    protected static function date(array $data, string $key): DateTimeImmutable
    {
        return new DateTimeImmutable(self::required($data, $key));
    }

    /**
     * @param array<array-key, mixed> $data
     */
    protected static function optionalDate(array $data, string $key): ?DateTimeImmutable
    {
        $value = self::optional($data, $key);

        return $value !== null ? new DateTimeImmutable($value) : null;
    }
}
