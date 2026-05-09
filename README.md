# eCourier PHP SDK

**A clean, modern PHP SDK for the [eCourier API](https://docs.ecourier.io), built with [Saloon](https://docs.saloon.dev).**

[![Tests](https://github.com/utecca/ecourier-php-sdk/actions/workflows/tests.yml/badge.svg)](https://github.com/utecca/ecourier-php-sdk/actions/workflows/tests.yml)
[![PHP](https://img.shields.io/badge/php-%5E8.3-blue)](https://www.php.net)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

Send and receive electronic invoices through the eCourier network from your PHP application. The SDK wraps the full [eCourier REST API v1](https://docs.ecourier.io/api-reference/v1) and gives you typed responses, automatic pagination, and clear exceptions for every error case.

---

## Installation

```bash
composer require ecourier/sdk
```

**Requirements:** PHP 8.3+

---

## Getting Started

Instantiate the connector with your API key. The same API endpoint is used for both test and live — the key prefix determines the mode: `pk_test_` for test, `pk_live_` for production.

```php
use Ecourier\Sdk\EcourierConnector;

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

echo $company->name;           // Acme Corporation
echo $company->cvr;            // 12345678
echo $company->address->city;  // Copenhagen
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

The simplest way to send an invoice. eCourier converts the JSON payload to the correct XML schema for the target channel automatically.

```php
$response = $ecourier->documents()->sendJson([
    'type'       => 'invoice',
    'reference'  => 'INV-2024-001',
    'issue_date' => '2024-06-01',
    'currency'   => 'DKK',
    'sender'     => ['cvr' => '12345678'],
    'receiver'   => ['cvr' => '87654321'],
    'lines'      => [
        ['description' => 'Consulting', 'quantity' => 1, 'unit_price' => 1250.00],
    ],
]);
```

> **Note:** Validation is asynchronous. A `200` response means the document was accepted, not yet delivered. Use webhooks or poll `find()` to track delivery.

### Send a document as raw XML

If you need full control over the XML schema, send the raw document directly.

```php
$xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2">
    <!-- your UBL invoice XML here -->
</Invoice>
XML;

$response = $ecourier->documents()->sendXml($xml);
```

### Get a single document

```php
$document = $ecourier->documents()->find('doc_01xyz');

echo $document->id;               // doc_01xyz
echo $document->status;           // delivered
echo $document->direction;        // outbound
echo $document->reference;        // INV-2024-001
echo $document->totalAmount;      // 1250.0
echo $document->currency;         // DKK
echo $document->sender->name;     // Acme Corporation
echo $document->receiver->name;   // Beta Ltd
echo $document->deliveredAt;      // 2024-06-01T10:05:00Z
```

### List documents (paginated)

`list()` returns a lazy paginator — pages are fetched on demand and you iterate individual `DocumentData` items without thinking about pages at all.

```php
foreach ($ecourier->documents()->list()->items() as $document) {
    echo $document->id . ': ' . $document->status . PHP_EOL;
}
```

Filter by status, direction, or date range:

```php
$paginator = $ecourier->documents()->list(
    status:    'delivered',
    direction: 'outbound',
    from:      '2024-01-01',
    to:        '2024-12-31',
    perPage:   50,
);

foreach ($paginator->items() as $document) {
    // ...
}
```

Collect all results into an array:

```php
$documents = iterator_to_array(
    $ecourier->documents()->list(status: 'pending')->items()
);
```

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

// Save the PDF
file_put_contents('invoice.pdf', $pdf);
```

---

## Participants

Look up whether a company is reachable on the eCourier network and which document types they accept.

```php
$participant = $ecourier->participants()->find('part_01ghi');

echo $participant->name;     // Beta Ltd
echo $participant->scheme;   // GLN
echo $participant->endpoint; // 5790000123456

foreach ($participant->documentTypes as $type) {
    echo $type . PHP_EOL;
    // urn:oasis:names:specification:ubl:schema:xsd:Invoice-2
    // urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2
}
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
use Ecourier\Sdk\Exceptions\AuthenticationException;
use Ecourier\Sdk\Exceptions\EcourierException;
use Ecourier\Sdk\Exceptions\NotFoundException;
use Ecourier\Sdk\Exceptions\ValidationException;

try {
    $document = $ecourier->documents()->find('doc_missing');
} catch (NotFoundException $e) {
    // document doesn't exist
    echo $e->getMessage();
} catch (ValidationException $e) {
    // inspect field-level errors
    foreach ($e->getErrors() as $field => $messages) {
        echo "{$field}: " . implode(', ', $messages) . PHP_EOL;
    }
} catch (AuthenticationException $e) {
    // bad API key
} catch (EcourierException $e) {
    // everything else
    $e->getResponse()->status(); // raw HTTP status
}
```

---

## Testing & Mocking

The SDK is built on Saloon, which ships with a first-class mock client. No HTTP requests are made in your tests.

### Mocking a response

```php
use Ecourier\Sdk\EcourierConnector;
use Ecourier\Sdk\Requests\Documents\GetDocumentRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

$mockClient = new MockClient([
    GetDocumentRequest::class => MockResponse::make(
        body: ['id' => 'doc_01xyz', 'status' => 'delivered', 'direction' => 'outbound'],
        status: 200,
    ),
]);

$ecourier = new EcourierConnector(apiKey: 'pk_test_fake');
$ecourier->withMockClient($mockClient);

$document = $ecourier->documents()->find('doc_01xyz');

expect($document->status)->toBe('delivered');
```

### Asserting requests were sent

```php
$mockClient->assertSent(GetDocumentRequest::class);
$mockClient->assertNotSent(SendDocumentAsJsonRequest::class);
$mockClient->assertSentCount(1);
```

### Mocking exceptions

```php
use Ecourier\Sdk\Requests\Companies\GetCompanyRequest;

$mockClient = new MockClient([
    GetCompanyRequest::class => MockResponse::make(
        body: ['message' => 'Not found.'],
        status: 404,
    ),
]);

$ecourier->withMockClient($mockClient);

// This will throw NotFoundException
$ecourier->companies()->find('comp_missing');
```

---

## Data Objects

All resources return typed DTOs with readonly properties, so your IDE can autocomplete everything.

### `CompanyData`

| Property | Type | Description |
|---|---|---|
| `$id` | `string` | Unique company ID |
| `$name` | `string` | Company name |
| `$cvr` | `?string` | Danish CVR number |
| `$vat` | `?string` | VAT number |
| `$country` | `?string` | ISO 3166-1 alpha-2 country code |
| `$email` | `?string` | Contact email |
| `$phone` | `?string` | Contact phone |
| `$address` | `?AddressData` | Postal address |
| `$createdAt` | `?string` | ISO 8601 timestamp |
| `$updatedAt` | `?string` | ISO 8601 timestamp |

### `DocumentData`

| Property | Type | Description |
|---|---|---|
| `$id` | `string` | Unique document ID |
| `$status` | `string` | `pending`, `delivered`, `failed`, etc. |
| `$direction` | `string` | `inbound` or `outbound` |
| `$type` | `?string` | `invoice` or `credit_note` |
| `$channel` | `?string` | Delivery channel (e.g. `nemhandel`) |
| `$reference` | `?string` | Your invoice reference number |
| `$issueDate` | `?string` | Invoice issue date |
| `$totalAmount` | `?float` | Total invoice amount |
| `$currency` | `?string` | ISO 4217 currency code |
| `$sender` | `?PartyData` | Sending party |
| `$receiver` | `?PartyData` | Receiving party |
| `$deliveredAt` | `?string` | ISO 8601 delivery timestamp |
| `$errors` | `?array` | Validation or delivery errors |

### `ParticipantData`

| Property | Type | Description |
|---|---|---|
| `$id` | `string` | Unique participant ID |
| `$name` | `string` | Participant name |
| `$scheme` | `?string` | Identifier scheme (e.g. `GLN`) |
| `$endpoint` | `?string` | Network endpoint identifier |
| `$country` | `?string` | ISO 3166-1 alpha-2 country code |
| `$documentTypes` | `?array` | Accepted document type URNs |

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
$response->array();     // alias for json()
$response->throw();     // throw if failed
```

### Sending requests directly

If you need to bypass the resource layer entirely, send requests directly through the connector:

```php
use Ecourier\Sdk\Requests\Documents\GetDocumentRequest;

$response = $ecourier->send(new GetDocumentRequest('doc_01xyz'));
```

---

## Running Tests

```bash
composer test
```

Code style:

```bash
# Check
vendor/bin/pint --test

# Fix
vendor/bin/pint
```

---

## License

MIT — see [LICENSE](LICENSE).
