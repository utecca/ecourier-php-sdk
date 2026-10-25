<?php

declare(strict_types=1);

namespace Ecourier\Data\Webhook;

final class WebhookSignature
{
    public const HEADER = 'Signature';

    /**
     * Verify the Signature header of a webhook request against the raw, unparsed request body.
     */
    public static function verify(string $body, ?string $signature, string $secret): bool
    {
        if ($signature === null || $signature === '' || $secret === '') {
            return false;
        }

        return hash_equals(self::sign($body, $secret), $signature);
    }

    public static function sign(string $body, string $secret): string
    {
        return hash_hmac('sha256', $body, $secret);
    }
}
