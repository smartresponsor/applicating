# Applicating active runtime architecture

## Active bounded context
The active runtime is a Symfony-oriented `Application` lifecycle component. The authoritative domain contour is:
- application listing metadata
- release/version lifecycle
- manifest/governance lifecycle
- tenant assignment and enable/disable lifecycle
- admin UI, admin JSON API and console operations
- readiness, diagnostics and reporting

## Active runtime directories
- `src/`
- `config/`
- `templates/`
- `tests/`
- `public/`
- `tools/`
- `helm/applicating/`
- `k8s/sealedsecrets/applicating-secrets.yaml`
- curated Applicating docs and workflows

## Explicit non-active contours
The following repository areas are not the active runtime source of truth for Applicating:
- `archive/repo-drift/legacy-tree-archive/legacy/`
- `archive/repo-drift/admin-ui-archive/`
- `archive/repo-drift/`
- historical product/catalog/platform experiments that are no longer wired into the live runtime

## Current runtime style
- monolithic Symfony application
- SQL persistence via Doctrine ORM
- synchronous application lifecycle writes
- admin-only JSON API
- persistent Doctrine-backed admin authentication, with login throttling and isolated in-memory test override

## Operational entry points
- web admin: `/admin/applications`
- JSON API: `/api/admin/application`, `/api/admin/v1/application`
- readiness API: `/api/admin/application/readiness/{slug}`, `/api/admin/v1/application/readiness/{slug}`
- liveness: `/health`
- readiness: `/ready`
