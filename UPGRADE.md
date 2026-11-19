# Upgrading from v1 to v2

## Namespaces

Data objects are split into `Ecourier\Data\Requests` (what you send) and `Ecourier\Data\Responses` (what you get back):

| v1 | v2 |
|---|---|
| `Ecourier\Data\Invoice\*` | `Ecourier\Data\Requests\Invoice\*` |
| `Ecourier\Data\Webhook\*` | `Ecourier\Data\Responses\Webhook\*` |
| `Ecourier\Data\Create*Data`, `Update*Data` | `Ecourier\Data\Requests\*` |
| Every other `Ecourier\Data\*` class | `Ecourier\Data\Responses\*` |

`AddressData` and `PartyData` are removed.

## Other changes

- `documents()->list()` yields `DocumentListItemData`. Call `find()` for the full `DocumentData`.
- `DocumentData::$dashboardUrl` is now `$webUrl`.
- `SendDocumentData` only has `$id`. Read `$latestE2eMessageUuid` from `find()` or a webhook.
- Unknown enum values become `null` instead of throwing, so `$status`, `$type`, `$submissionFormat` and `$scheme` are nullable.
- A missing required response field throws `MissingResponseFieldException`.
- `companies()->update()` and `participants()->update()` take `UpdateCompanyData` and `UpdateParticipantData`.
- `CreateCompanyData::$parentId` is removed.
- `InvoiceLineData`: `$id` is an `int`, `$name` is required and `$itemId` must be a GTIN.
- `InvoicePartyData::$name`, `InvoicePartyData::$simplifiedAddress` and `InvoicePaymentMeansData::$account` are required.
- `list()` fetches 25 items per page instead of 10.
- If you construct data objects yourself, for example in tests, use named arguments: argument order changed on several of them, and `DocumentData` has new required properties.
- Data objects are `final`, and response data objects and webhook events no longer have `toArray()`. Use `get()` for the raw response, or the webhook request body.
