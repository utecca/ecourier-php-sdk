# eCourier PHP SDK

**A clean, modern PHP SDK for the [eCourier API](https://docs.ecourier.io), built with [Saloon](https://docs.saloon.dev).**

[![Tests](https://github.com/utecca/ecourier-php-sdk/actions/workflows/tests.yml/badge.svg)](https://github.com/utecca/ecourier-php-sdk/actions/workflows/tests.yml)
[![PHP](https://img.shields.io/badge/php-%5E8.3-blue)](https://www.php.net)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

Send and receive electronic invoices through the eCourier network from your PHP application. The SDK wraps the full [eCourier REST API v1](https://docs.ecourier.io/api-reference/v1) and gives you typed responses, automatic pagination, and clear exceptions for every error case.

---

## Installation

```bash
composer require ecourier/ecourier
```

**Requirements:** PHP 8.3+

---

## Getting Started

Instantiate the connector with your API key. The key prefix determines the mode: `pk_test_` for test, `pk_live_` for production.

```php
use Ecourier\EcourierConnector;

// Test mode
$ecourier = new EcourierConnector(apiKey: 'pk_test_your_key_here');

// Production
$ecourier = new EcourierConnector(apiKey: 'pk_live_your_key_here');
```

All requests are authenticated automatically via `Authorization: Bearer` — you never touch headers yourself.

---

## Resources

The SDK is organized into three resources, accessible as methods on the connector.

| Resource | Method | Covers |
|---|---|---|
| Companies | `$ecourier->companies()` | Look up company details |
| Documents | `$ecourier->documents()` | Send, receive, and inspect invoices |
| Participants | `$ecourier->participants()` | Look up network participants |

---

## Companies

### Get a company

```php
$company = $ecourier->companies()->find('comp_01abc');

echo $company->name;      // Acme Danmark A/S
echo $company->companyNo; // 12345678
echo $company->mode;      // Mode::Live
```

`find()` returns a typed `CompanyData` DTO. If you need the raw `Response` object instead, use `get()`:

```php
$response = $ecourier->companies()->get('comp_01abc');

$response->status(); // 200
$response->json();   // raw array
```

---

## Documents

Documents are the core of eCourier — they represent invoices and credit notes moving through the network.

### Send a document as JSON

Build a typed `InvoiceDocumentData` payload and submit it to a specific channel. eCourier converts it to the correct XML schema automatically.

```php
use Ecourier\Data\Invoice\InvoiceDocumentData;
use Ecourier\Data\Invoice\InvoiceLineData;
use Ecourier\Data\Invoice\InvoicePartyData;
use Ecourier\Data\Invoice\InvoiceTotalsData;
use Ecourier\Data\Invoice\ParticipantIdentifier;
use Ecourier\Enums\Channel;
use Ecourier\Enums\Currency;
use Ecourier\Enums\DocumentType;
use Ecourier\Enums\IdentifierScheme;

$invoice = new InvoiceDocumentData(
    type: DocumentType::Invoice,
    id: 'INV-2024-001',
    issueDate: '2024-06-01',
    currency: Currency::DKK,
    supplier: new InvoicePartyData(
        participant: new ParticipantIdentifier(IdentifierScheme::DK_CVR, '12345678'),
    ),
    customer: new InvoicePartyData(
        participant: new ParticipantIdentifier(IdentifierScheme::DK_CVR, '87654321'),
    ),
    lines: [
        new InvoiceLineData(id: 1),
    ],
    totals: new InvoiceTotalsData(
        subtotalAmount: '1000.00',
        taxAmount: '250.00',
        totalAmount: '1250.00',
    ),
);

$document = $ecourier->documents()->sendJson(Channel::Peppol, $invoice);

echo $document->id;             // 01kmkdaf55vrrecfy70180tpr6
echo $document->e2eMessageUuid; // ddc3b3ef-cbd4-4630-9d65-896b3e1abc61
```

> **Note:** `sendJson()` returns the accepted document ID and network message UUID. Use webhooks or poll `find()` to track delivery.

### Send a document as raw XML

If you need full control over the XML schema, send the raw UBL document directly. All routing headers are required.

```php
use Ecourier\Enums\Channel;
use Ecourier\Enums\IdentifierScheme;

$xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2">
    <!-- your UBL invoice XML here -->
</Invoice>
XML;

$document = $ecourier->documents()->sendXml(
    xml: $xml,
    channel: Channel::Peppol,
    senderScheme: IdentifierScheme::DK_CVR,
    senderId: '12345678',
    recipientScheme: IdentifierScheme::GLN,
    recipientId: '5790000123456',
);
```

### Get a single document

```php
use Ecourier\Enums\Channel;
use Ecourier\Enums\Direction;
use Ecourier\Enums\DocumentStatus;
use Ecourier\Enums\DocumentType;
use Ecourier\Enums\Mode;
use Ecourier\Enums\SubmissionFormat;

$document = $ecourier->documents()->find('doc_01xyz');

$document->id;               // '01kmkdaf55vrrecfy70180tpr6'
$document->status;           // DocumentStatus::Delivered
$document->channel;          // Channel::NemHandel
$document->mode;             // Mode::Live
$document->direction;        // Direction::Send
$document->type;             // DocumentType::Invoice
$document->submissionFormat; // SubmissionFormat::JSON
$document->sender->scheme;   // IdentifierScheme::DK_CVR
$document->recipient->id;    // '5790000123456'
$document->company->name;    // 'Acme Danmark A/S'
```

### List documents

`list()` returns a lazy paginator — see [Pagination](#pagination) for the full API.

### Retrieve document content

Get the raw XML of a delivered document:

```php
$response = $ecourier->documents()->contentAsXml('doc_01xyz');

$xml = $response->body(); // raw XML string
```

### Render a document

Get an HTML or PDF rendering (experimental):

```php
$html = $ecourier->documents()->renderAsHtml('doc_01xyz')->body();
$pdf  = $ecourier->documents()->renderAsPdf('doc_01xyz')->body();

file_put_contents('invoice.pdf', $pdf);
```

---

## Participants

Look up whether a company is reachable on an eCourier network.

```php
use Ecourier\Enums\Channel;
use Ecourier\Enums\IdentifierScheme;

$participant = $ecourier->participants()->find(
    channel: Channel::Peppol,
    scheme: IdentifierScheme::GLN,
    participantId: '5790000123456',
);

echo $participant->entityName; // GLN Denmark
echo $participant->mode->value; // Live
echo $participant->orgNo;      // 9999796418186
```

---

## Pagination

`list()` returns a `DocumentsPaginator` — a lazy iterator built on Saloon's `PagedPaginator`. Pages are fetched from the API on demand, one at a time, as you consume items.

### Iterating all pages

The simplest approach. Each page is fetched automatically when the previous one is exhausted:

```php
foreach ($ecourier->documents()->list()->items() as $document) {
    echo $document->id;
}
```

### LazyCollection

If you're in a Laravel application, `collect()` wraps the paginator in a `LazyCollection`, giving you the full collection API without loading everything into memory:

```php
$ecourier->documents()->list(perPage: 50)
    ->collect()
    ->each(function (DocumentData $document) {
        // processed one at a time, page by page
    });

// Chain collection methods — pages are fetched as items are consumed
$ecourier->documents()->list()
    ->collect()
    ->filter(fn ($doc) => $doc->company?->name === 'Acme Danmark A/S')
    ->each(fn ($doc) => ProcessDocument::dispatch($doc));
```

### First page only

Use `setMaxPages(1)` to stop after a single page. Exactly one HTTP request is made:

```php
$documents = $ecourier->documents()
    ->list(perPage: 25)
    ->setMaxPages(1)
    ->collect()
    ->all();
```

### A specific page

Combine `setStartPage()` and `setMaxPages()` to jump to any page:

```php
$documents = $ecourier->documents()
    ->list(perPage: 25)
    ->setStartPage(3)
    ->setMaxPages(1)
    ->collect()
    ->all();
```

### Filtering

All filters are applied at the API level — only matching documents are returned:

```php
use Ecourier\Enums\DocumentStatus;
use Ecourier\Enums\Channel;
use Ecourier\Enums\Direction;
use Ecourier\Enums\Sort;

$ecourier->documents()
    ->list(
        status:     DocumentStatus::Delivered,
        channel:    Channel::Peppol,
        companyId:  '0101knwp96k3ggvkra831yrd74zh',
        direction:  Direction::Send,
        sort:       Sort::CreatedAtDesc,
        perPage:    50,
    )
    ->collect()
    ->each(fn ($doc) => ...);
```

---

## Exception Handling

The SDK throws typed exceptions for every error case — no checking status codes manually.

| Exception | HTTP Status | When |
|---|---|---|
| `AuthenticationException` | 401 | Invalid or missing API key |
| `NotFoundException` | 404 | Resource does not exist |
| `ValidationException` | 422 | Invalid request payload |
| `EcourierException` | 5xx / other | Unexpected server errors |

All exceptions extend `EcourierException`, so you can catch broadly or narrowly:

```php
use Ecourier\Exceptions\AuthenticationException;
use Ecourier\Exceptions\EcourierException;
use Ecourier\Exceptions\NotFoundException;
use Ecourier\Exceptions\ValidationException;

try {
    $document = $ecourier->documents()->find('doc_missing');
} catch (NotFoundException $e) {
    echo $e->getMessage();
} catch (ValidationException $e) {
    foreach ($e->getErrors() as $field => $messages) {
        echo "{$field}: " . implode(', ', $messages) . PHP_EOL;
    }
} catch (AuthenticationException $e) {
    // bad API key
} catch (EcourierException $e) {
    $e->getResponse()->status();
}
```

---

## Enums

All typed fields use PHP backed enums, giving you IDE autocomplete and preventing invalid values at the type level.

| Enum | Values |
|---|---|
| `DocumentStatus` | `Pending`, `Ready`, `Delivered`, `Failed` |
| `DocumentType` | `Invoice`, `CreditNote`, `ApplicationResponse`, `EndUserStatisticsReport`, `TransactionStatisticsReport`, `Other` |
| `Direction` | `Send`, `Receive` |
| `Channel` | `Peppol`, `NemHandel` |
| `Mode` | `Live`, `Test` |
| `SubmissionFormat` | `XML`, `GOBL`, `JSON` |
| `Sort` | `CreatedAt`, `CreatedAtDesc` |
| `Currency` | `EUR`, `DKK`, `USD`, `GBP`, and 29 others (ISO 4217) |
| `IdentifierScheme` | `DK_CVR`, `GLN`, `EU_VAT`, and 80+ others |
| `TaxCategoryCode` | `S`, `AA`, `Z`, `E`, `AE`, `K`, `G`, `O`, `L`, `M` |
| `PaymentMeansCode` | `CreditTransfer`, `DebitTransfer`, `PaymentToAccount` |
| `AccountSchemeId` | `IBAN`, `DK_BBAN` |

Enums serialize to their wire value automatically when used in requests. When the API returns an unrecognised value for an optional enum field, the SDK maps it to `null` rather than throwing.

---

## Testing & Mocking

The SDK is built on Saloon, which ships with a first-class mock client. No HTTP requests are made in your tests.

### Mocking a response

```php
use Ecourier\EcourierConnector;
use Ecourier\Enums\Direction;
use Ecourier\Enums\DocumentStatus;
use Ecourier\Requests\Documents\GetDocumentRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

$mockClient = new MockClient([
    GetDocumentRequest::class => MockResponse::make(
        body: ['id' => 'doc_01xyz', 'status' => 'Delivered', 'direction' => 'Send'],
        status: 200,
    ),
]);

$ecourier = new EcourierConnector(apiKey: 'pk_test_fake');
$ecourier->withMockClient($mockClient);

$document = $ecourier->documents()->find('doc_01xyz');

expect($document->status)->toBe(DocumentStatus::Delivered);
expect($document->direction)->toBe(Direction::Send);
```

### Asserting requests were sent

```php
$mockClient->assertSent(GetDocumentRequest::class);
$mockClient->assertNotSent(SendDocumentAsJsonRequest::class);
$mockClient->assertSentCount(1);
```

### Mocking exceptions

```php
use Ecourier\Requests\Companies\GetCompanyRequest;

$mockClient = new MockClient([
    GetCompanyRequest::class => MockResponse::make(
        body: ['message' => 'Not found.'],
        status: 404,
    ),
]);

$ecourier->withMockClient($mockClient);

// Throws NotFoundException
$ecourier->companies()->find('comp_missing');
```

---

## Data Objects

All resources return typed DTOs with readonly properties.

### `CompanyData`

| Property | Type |
|---|---|
| `$id` | `string` |
| `$name` | `string` |
| `$mode` | `Mode` |
| `$companyNo` | `string` |
| `$createdAt` | `DateTimeImmutable` |
| `$updatedAt` | `DateTimeImmutable` |
| `$parentId` | `?string` |
| `$children` | `array` |
| `$country` | `string` |
| `$authorisation` | `?CompanyAuthorisationData` |
| `$participants` | `CompanyParticipantData[]` |

### `DocumentData`

| Property | Type |
|---|---|
| `$id` | `string` |
| `$status` | `DocumentStatus` |
| `$channel` | `Channel` |
| `$mode` | `?Mode` |
| `$direction` | `Direction` |
| `$type` | `DocumentType` |
| `$submissionFormat` | `?SubmissionFormat` |
| `$sender` | `?ParticipantIdentifier` |
| `$recipient` | `?ParticipantIdentifier` |
| `$e2eMessageUuid` | `?string` |
| `$company` | `?DocumentCompanyData` |
| `$createdAt` | `?DateTimeImmutable` |

### `ParticipantData`

| Property | Type |
|---|---|
| `$channel` | `Channel` |
| `$mode` | `Mode` |
| `$entityName` | `string` |
| `$country` | `string` |
| `$registrationDate` | `string` |
| `$orgNo` | `string` |
| `$registryUrl` | `string` |

### `SendDocumentData`

| Property | Type |
|---|---|
| `$id` | `string` |
| `$e2eMessageUuid` | `string` |

### `InvoiceDocumentData` (request payload)

| Property | Type | Required |
|---|---|---|
| `$type` | `DocumentType` | Yes |
| `$id` | `string` | Yes |
| `$issueDate` | `string` | Yes |
| `$currency` | `Currency` | Yes |
| `$supplier` | `InvoicePartyData` | Yes |
| `$customer` | `InvoicePartyData` | Yes |
| `$lines` | `InvoiceLineData[]` | Yes |
| `$totals` | `InvoiceTotalsData` | Yes |
| `$uuid` | `?string` | No |
| `$dueDate` | `?string` | No |
| `$orderReference` | `?string` | No |
| `$payment` | `?InvoicePaymentData` | No |

### `InvoiceLineData` (request payload)

| Property | Type | Required |
|---|---|---|
| `$id` | `int` | Yes |
| `$name` | `?string` | No |
| `$description` | `?string` | No |
| `$quantity` | `?string` | No |
| `$unitCode` | `?string` | No |
| `$unitPrice` | `?string` | No |
| `$lineTotal` | `?string` | No |
| `$taxCategory` | `?InvoiceTaxCategoryData` | No |
| `$itemId` | `?string` | No |
| `$sellersItemId` | `?string` | No |
| `$buyersItemId` | `?string` | No |

---

## Advanced Usage

### Accessing the raw Saloon response

Every resource method has a companion that returns the raw `Saloon\Http\Response` instead of a DTO. Use `get()` instead of `find()`:

```php
$response = $ecourier->companies()->get('comp_01abc');

$response->status();    // 200
$response->headers();   // response headers
$response->body();      // raw body string
$response->json();      // decoded array
```

### Sending requests directly

If you need to bypass the resource layer entirely:

```php
use Ecourier\Requests\Documents\GetDocumentRequest;

$response = $ecourier->send(new GetDocumentRequest('doc_01xyz'));
```

---

## Running Tests

```bash
composer test
```

Code style:

```bash
vendor/bin/pint --test  # check
vendor/bin/pint         # fix
```

---

## License

MIT — see [LICENSE](LICENSE).
