<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses\Concerns;

use BackedEnum;
use DateMalformedStringException;
use DateTimeImmutable;
use Ecourier\Exceptions\InvalidResponseFieldException;
use Ecourier\Exceptions\MissingResponseFieldException;
use ReflectionEnum;

/**
 * Reads API response fields with one set of rules for every response data object:
 *
 * - A field for a non-nullable property is required. If it is missing or null,
 *   a {@see MissingResponseFieldException} is thrown.
 * - A field for a nullable property is optional and becomes null when missing.
 * - A list field is optional and becomes an empty list when missing.
 * - A field with a type the SDK cannot read, e.g. a number where a string is expected,
 *   throws an {@see InvalidResponseFieldException}.
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
     */
    protected static function string(array $data, string $key): string
    {
        $value = self::required($data, $key);

        if (! is_string($value)) {
            throw new InvalidResponseFieldException(static::class, $key, 'a string', $value);
        }

        return $value;
    }

    /**
     * @param array<array-key, mixed> $data
     */
    protected static function optionalString(array $data, string $key): ?string
    {
        return isset($data[$key]) ? self::string($data, $key) : null;
    }

    /**
     * A required nested object.
     *
     * @param array<array-key, mixed> $data
     * @return array<array-key, mixed>
     */
    protected static function object(array $data, string $key): array
    {
        $value = self::required($data, $key);

        if (! is_array($value)) {
            throw new InvalidResponseFieldException(static::class, $key, 'an object', $value);
        }

        return $value;
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
     * A list of nested objects.
     *
     * @param array<array-key, mixed> $data
     * @return list<array<array-key, mixed>>
     */
    protected static function objects(array $data, string $key): array
    {
        $objects = [];

        foreach (self::list($data, $key) as $value) {
            if (! is_array($value)) {
                throw new InvalidResponseFieldException(static::class, $key, 'a list of objects', $value);
            }

            $objects[] = $value;
        }

        return $objects;
    }

    /**
     * @template TEnum of BackedEnum
     * @param class-string<TEnum> $enum
     * @return TEnum|null
     */
    protected static function enum(string $enum, mixed $value): ?BackedEnum
    {
        return self::hasBackingType($enum, $value) ? $enum::tryFrom($value) : null;
    }

    /**
     * @template TEnum of BackedEnum
     * @param array<array-key, mixed> $data
     * @param class-string<TEnum> $enum
     * @return TEnum
     */
    protected static function requiredEnum(array $data, string $key, string $enum): BackedEnum
    {
        return self::enumFrom($enum, self::required($data, $key), $key);
    }

    /**
     * @template TEnum of BackedEnum
     * @param array<array-key, mixed> $data
     * @param class-string<TEnum> $enum
     * @return list<TEnum>
     */
    protected static function enumList(array $data, string $key, string $enum): array
    {
        return array_map(fn(mixed $value) => self::enumFrom($enum, $value, $key), self::list($data, $key));
    }

    /**
     * @param array<array-key, mixed> $data
     */
    protected static function date(array $data, string $key): DateTimeImmutable
    {
        return self::dateFrom(self::string($data, $key), $key);
    }

    /**
     * @param array<array-key, mixed> $data
     */
    protected static function optionalDate(array $data, string $key): ?DateTimeImmutable
    {
        $value = self::optionalString($data, $key);

        return $value !== null ? self::dateFrom($value, $key) : null;
    }

    /**
     * An unknown value of the right type still throws a ValueError: the enums read this way are strict on purpose.
     *
     * @template TEnum of BackedEnum
     * @param class-string<TEnum> $enum
     * @return TEnum
     */
    private static function enumFrom(string $enum, mixed $value, string $key): BackedEnum
    {
        if (! self::hasBackingType($enum, $value)) {
            $expected = self::backingType($enum) === 'int' ? 'an integer' : 'a string';

            throw new InvalidResponseFieldException(static::class, $key, $expected, $value);
        }

        return $enum::from($value);
    }

    /**
     * @param class-string<BackedEnum> $enum
     * @phpstan-assert-if-true int|string $value
     */
    private static function hasBackingType(string $enum, mixed $value): bool
    {
        return get_debug_type($value) === self::backingType($enum);
    }

    /**
     * @param class-string<BackedEnum> $enum
     * @return 'int'|'string'
     */
    private static function backingType(string $enum): string
    {
        return (new ReflectionEnum($enum))->getBackingType()?->getName() === 'int' ? 'int' : 'string';
    }

    private static function dateFrom(string $value, string $key): DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($value);
        } catch (DateMalformedStringException $exception) {
            throw new InvalidResponseFieldException(static::class, $key, 'a date', $value, $exception);
        }
    }
}
