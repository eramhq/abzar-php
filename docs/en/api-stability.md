# API Stability

Abzar follows [Semantic Versioning](https://semver.org/). This page spells out which parts of the surface area are covered by the BC promise and which are explicitly not.

## Current status: `0.x`

While abzar is in `0.x`, breaking changes can happen in any minor release. Pin with `^0.3@beta` and review the [CHANGELOG](../../CHANGELOG.md) before upgrading.

From `1.0.0` onward, the commitments below apply.

## Stable (BC-protected)

- **Public class names and namespaces** (`Eram\Abzar\...`).
- **Public method signatures**: parameter types, return types, and method names.
- **`ValidationResult` public shape**:
  - `isValid(): bool`
  - `errors(): list<string>`
  - `errorCodes(): list<ErrorCode>`
  - `warnings(): list<string>`
  - `warningCodes(): list<ErrorCode>`
  - `details(): array<string, mixed>`
  - `bank() / operator() / province()` typed accessors
  - `jsonSerialize()` output shape.
- **`Eram\Abzar\Validation\ErrorCode`** — the backing string value for each case is API surface from `0.3` onward. Renaming or dropping a case is a breaking change. New cases may be added in minor releases.
- **Input-accepting conventions**: Persian / Arabic / English digits are accepted interchangeably across all validators and formatters.

## Explicitly unstable

- **Persian error message text**. `ValidationResult::errors()` returns human-facing Persian strings. They may be reworded for clarity, punctuation, or tone between minor releases. Do not pattern-match on them; use error codes (when available) or the overall `isValid()` boolean.
- **`details()` value strings** that come from lookup tables: bank names, city names, province names, operator names. These reflect real-world data that changes (mergers, renames, splits). The **keys** are stable; the strings are not.
- **Lookup-table contents**: entries are added, removed, and corrected as upstream data is updated. Consumers relying on a specific BIN or city-code mapping should snapshot the value in their own code if they need exact reproducibility.
- **Internal classes and methods**. Anything marked `@internal`, anything under a namespace not explicitly documented, and private / protected members.

## Deprecation policy (from `1.0.0`)

1. A deprecation is announced in a minor release with `@deprecated` on the source and an entry in `CHANGELOG.md`.
2. The deprecated surface keeps working for the remainder of that major.
3. Removal happens only in the next major release.

## Data-file changes

Lookup-table changes (city codes, bank BINs, operator prefixes, IBAN issuers) ship in minor or patch releases without a deprecation cycle because they reflect external reality, not API surface. If a change would flip a previously-valid input to invalid (or vice versa), it is called out in the changelog.
