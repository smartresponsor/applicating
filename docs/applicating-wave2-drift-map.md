# Applicating wave 2 drift map

This document records the active engineering drift still visible after the first Applicating-oriented report additions.

## Confirmed active drift

### composer report wiring

`composer.json` still points the report commands to legacy inspection entry points:

- `tools/inspection/CatalogOwnerOverlapReport.php`
- `tools/inspection/CatalogRouteInventoryReport.php`
- `tools/inspection/CatalogClassAliasReport.php`
- `tools/inspection/CatalogRuntimeProofReport.php`

### release documentation wording

`docs/CHANGELOG.md` still describes the component as `Smartresponsor Product Suite v18.0.0`.

`docs/RELEASE.md` still publishes release artifacts such as:

- `product-suite-final-cut.zip`
- `product-suite-roadmap.zip`
- `product-suite-roadmap-timeline.zip`
- `product-suite-demo-bootstrap.zip`

### temporary write-check file

A temporary file created during connector write-path probing is still present:

- `tmp-chatgpt-write-check.txt`

## Follow-up target

The next direct file-update wave should:

1. rewire report commands in `composer.json` to `Applicating*` inspection reports;
2. rewrite `docs/CHANGELOG.md` to Applicating/Application wording;
3. rewrite `docs/RELEASE.md` to Applicating/Application release wording;
4. remove `tmp-chatgpt-write-check.txt`.
