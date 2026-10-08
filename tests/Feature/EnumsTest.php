<?php

declare(strict_types=1);

use Ecourier\Enums\Channel;
use Ecourier\Enums\IdentifierScheme;

it('maps every identifier scheme to a unique ICD code', function () {
    $codes = array_map(fn(IdentifierScheme $scheme) => $scheme->icd(), IdentifierScheme::cases());

    expect($codes)->each->toMatch('/^\d{4}$/')
        ->and(array_unique($codes))->toHaveCount(count(IdentifierScheme::cases()));
});

it('returns the ICD code of an identifier scheme', function (IdentifierScheme $scheme, string $icd) {
    expect($scheme->icd())->toBe($icd);
})->with([
    [IdentifierScheme::DK_CVR, '0184'],
    [IdentifierScheme::DK_P, '0096'],
    [IdentifierScheme::DK_SE, '0198'],
    [IdentifierScheme::GLN, '0088'],
    [IdentifierScheme::NO_ORG, '0192'],
    [IdentifierScheme::DE_VAT, '9930'],
]);

it('only supports Danish identifiers and GLN on NemHandel', function () {
    expect(Channel::NemHandel->supportedSchemes())->toBe([
        IdentifierScheme::DK_CVR,
        IdentifierScheme::DK_SE,
        IdentifierScheme::DK_P,
        IdentifierScheme::GLN,
    ]);
});

it('supports every identifier scheme on Peppol', function () {
    expect(Channel::Peppol->supportedSchemes())->toBe(IdentifierScheme::cases());
});
