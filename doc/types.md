# Product Types
- simple: no variants
- configurable: variants[] required
- bundle: bundle[] of items (sku, qty, required)
- grouped: grouped[] product ids
- virtual: virtual=true (no shipping)
- downloadable: download{ url, licenseKey? }

Invariants:
- For configurable, variants[] MUST NOT be empty
- Variant option combinations MUST be unique per product
- Bundle item SKUs MUST be distinct within the product
