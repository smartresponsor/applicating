# Applicating wave 6 doc alignment map

## Focus
This wave aligns overview documentation with the actual Application Workspace runtime already present in the repository.

## What was drifting
The previous overview cluster still described:
- plugin marketplace vocabulary;
- `marketplace_*` style concepts;
- speculative devhub / plugin SDK entrypoints;
- governance language centered on plugin install/publish rather than application lifecycle.

## What this wave aligns
- `APPLICATION_WORKSPACE_OVERVIEW.md` becomes the canonical high-level overview.
- `GOVERNANCE_OVERVIEW.md` now reflects manifest/release/application publish rules.
- `SDK_OVERVIEW.md` now describes the real developer surface that exists today.
- legacy `MARKETPLACE_OVERVIEW.md` is removed from the active docs contour.

## Result
The docs cluster now describes the same model as the runtime:
- Application
- ApplicationRelease
- ApplicationManifest
- TenantApplication
- admin / API / CLI publish surfaces
- publish eligibility and graceful failure handling
