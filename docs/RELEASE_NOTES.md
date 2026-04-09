# Release notes — Applicating active contour cleanup

## What changed
- the repository's active operational contour was narrowed to the Applicating runtime
- misleading product-suite, catalog, mesh and federation operational artifacts were archived under `archive/repo-drift/`
- active workflows were reduced to a curated Applicating set
- active Helm delivery was narrowed to `helm/applicating`
- active documentation was reduced to Applicating-specific runtime, operations and security docs

## Why it matters
The repository now tells a truer story:
- what is active
- what is historical
- what should be deployed
- which workflows should still run

## Compatibility note
No application-domain routes or entities were removed as part of this cleanup wave. The main effect is operational clarity and lower repo-level drift.
