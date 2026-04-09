# Changelog

## 2026-03-27

- rebuilt the active runtime as a canonical Symfony application with `App\ => src/`
- moved legacy mixed, historical UI and retail-oriented trees out of the active contour into `archive/repo-drift/`
- introduced `Application`, `ApplicationRelease`, `ApplicationManifest` and `TenantApplication` as the active model center
- replaced application-facing `Product` vocabulary in the active runtime with `Application` vocabulary across controllers, CLI, templates, fixtures and API payloads
- added Application lifecycle services, mirrored `ServiceInterface/` contracts, Symfony forms, Bootstrap Twig UI and security voter rules
- added Symfony Console commands for fixtures, publication, diagnostics, manifest validation, reporting and tenant assignment checks
- added unit, integration, functional, Panther and Playwright coverage focused on application lifecycle scenarios
- added application-specific lint, smoke and local pipeline scripts with report output
