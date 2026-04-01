# Applicating wave 2 patch manifest

This manifest records the direct edits that should be applied when the connector write surface allows updates to existing files.

## 1. `composer.json`

Replace the active report command wiring:

- `tools/inspection/CatalogOwnerOverlapReport.php` -> `tools/inspection/ApplicatingOwnerOverlapReport.php`
- `tools/inspection/CatalogRouteInventoryReport.php` -> `tools/inspection/ApplicatingRouteInventoryReport.php`
- `tools/inspection/CatalogClassAliasReport.php` -> `tools/inspection/ApplicatingClassAliasReport.php`
- `tools/inspection/CatalogRuntimeProofReport.php` -> `tools/inspection/ApplicatingRuntimeProofReport.php`

Add a canonical roots linter command wired to:

- `tools/linter/applicating_canonical_roots_check.php`

Optionally add a dedicated report command wired to:

- `tools/inspection/ApplicatingEngineeringDriftReport.php`
- `tools/inspection/ApplicatingPipelineWiringReport.php`

## 2. `docs/CHANGELOG.md`

Rewrite release vocabulary from Product Suite / Product entity / Catalog adapter to Applicating / Application wording.

## 3. `docs/RELEASE.md`

Rewrite release title and artifact names away from `product-suite-*` to Applicating/Application-oriented naming.

## 4. `tools/linter/category_canonical_roots_check.php`

Replace active callers with the Applicating-oriented successor:

- `tools/linter/applicating_canonical_roots_check.php`

Then retire the legacy `category_*` linter path from the active engineering contour.

## 5. Repository cleanup

Remove the temporary connector probe file:

- `tmp-chatgpt-write-check.txt`
