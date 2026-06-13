# Applicating entity-first migration retirement

## Scope

This pass treats `Applicating` as an entity-first Symfony component. Retired Doctrine migrations are no longer the source of schema truth.

## Retired schema-first source

- `Applicating/migrations/**`

## Migration coverage

The retired migrations define these runtime tables and constraints:

- `application_listing`
- `application_release`
- `application_manifest`
- `tenant_application`
- `application_user`

All tables already had entity-first models in `src/Entity`:

- `Application`
- `ApplicationRelease`
- `ApplicationManifest`
- `TenantApplication`
- `ApplicationUser`

This patch therefore does not create parallel duplicate entities.

## Metadata lifted from migrations

- `ApplicationUser` now carries retired SQL indexes in Doctrine metadata:
  - `idx_application_user_active`
  - `idx_application_user_auth_source`
- `Application` now uses explicit named unique constraints for:
  - `slug`
  - `package_name`

Existing migration indexes and unique constraints on `ApplicationRelease`, `ApplicationManifest`, and `TenantApplication` were already present in Doctrine attributes and were preserved.

## Repository contracts

Added repository interfaces for all Applicating entities and made concrete repositories implement them:

- `ApplicationRepositoryInterface`
- `ApplicationReleaseRepositoryInterface`
- `ApplicationManifestRepositoryInterface`
- `ApplicationUserRepositoryInterface`
- `TenantApplicationRepositoryInterface`

## Objecting decision

No new generic Objecting embeddables were injected into existing runtime entities in this pass. The component already has constructor and lifecycle behavior around `createdAt`, `updatedAt`, `assignedAt`, `publishedAt`, `lastLoginAt`, and state fields. Replacing those fields blindly would be a partial refactor and risks breaking current services.

The important rule is preserved: no new duplicate system fields were added.

## Legacy monolith reconciliation

`Entity-src(6).zip` does not contain an old `Application`, `Applicating`, or `TenantApplication` monolith subtree. There were no legacy business relationships to restore from the old monolith for this component.

## Validation

PHP syntax lint was run for patched PHP files. Full Doctrine metadata validation still requires the runtime host with dependencies installed.
