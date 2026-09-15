# CMCP execution journal

## 2026-09-15 — Applicating RC hardening

### Reconnaissance baseline

- Read the repository `AGENTS.md`, `README.md`, all tracked Markdown/AsciiDoc documentation under `docs/`, `composer.json`, Symfony bundle configuration, active source changes, tests, QA scripts, and Git working state.
- Read the relevant contracts from `Objecting`, `Cruding`, `Viewing`, `Interfacing`, `Collectioning`, `Tabling`, `Gating`, and authoritative textual rules in `Canonization`.
- The current `master` worktree already contains an unfinished canonical structure migration: explicit `*DTO` types under `src/DTO/`, flattened Form/DTO subject buckets, an `Applicating*Command` rename, caller/test rewiring, and local Gating materialization.
- The existing structural changes pass PHP syntax lint, `lint:canonical-roots`, `lint:app-namespace`, and `lint:service-interface-parity`.

### Canonization mapping

- `Canon000`: Applicating business PHP types use the natural `Application*` subject vocabulary; Applicating-prefixed framework/CLI infrastructure remains appropriate where it identifies the component itself.
- `Canon001` / `Canon003` / `Canon004`: keep technical-role-first roots, explicit `DTO` suffix/casing, and no premature `Application/` subject bucket under DTO/Form. The in-progress DTO/Form migration is therefore RC-critical and should be completed rather than reverted.
- `Canon008` / `Canon022`: this standalone Symfony application must declare the direct runtime platform dependency baseline: Objecting, Cruding, Collectioning, Tabling, Viewing, Interfacing, and EasyAdmin.
- `Canon023` / `Canon043` / `Canon045`: development sibling packages must be path repositories with `symlink: true`, exact `dev-master` package identity, and complete first-party local repository closure.
- `Canon024` / `Canon033`: add a production Composer manifest with matching package identity and packaged dependencies, without local path repositories.
- `Canon021`: generic CRUD remains owned by Cruding; Applicating keeps only application-lifecycle business behavior.
- `Canon037`: tracked `config/reference.php` is generated output and must be removed from Git source and ignored.
- `Canon017`: repository documentation must match the actual `App\\Applicating\\...` runtime namespace and current package contract.

### Selected work

- **RC-critical:** finish the canonical DTO/Form/Command migration already present; repair Composer development/production package contracts; remove generated reference source; correct factual integration documentation; run static, test, Symfony/Doctrine, smoke, and release gates and repair in-scope failures.
- **Growth (post-RC):** richer software-catalog scorecards, release/environment promotion UX, self-service templates, and expanded cross-runtime observability. These do not block RC unless required by an existing correctness or operability invariant.

### Material risks

- Composer repository closure spans several local sibling packages and may expose dependency-cycle or lock-resolution defects.
- Doctrine/schema gates may depend on local PostgreSQL availability; failures must be classified as code defects versus environment blockers from actual output.
- The pre-existing worktree is substantial; no unrelated rollback or destructive cleanup is permitted.

### Gates

- `composer validate --strict --check-lock`: PASS.
- `composer audit`: PASS; no security vulnerability advisories reported.
- Canon/style contour: PHP lint, App namespace, config-prefix, canonical-roots, service-interface parity, and PHP-CS-Fixer all PASS. The generated `config/reference.php` remains untracked/ignored and is excluded from source-style enforcement per Canon037.
- `qa:static`: PASS at PHPStan max with Doctrine/Symfony extensions and no project-specific suppression required after mapping repair.
- `qa:smell`: PASS after updating the repository scripts to PHPMD 3 `analyze` syntax and decomposing readiness/user-create command branching.
- `qa:test`: PASS — 14 tests, 42 assertions. Unit, integration, and functional suites also passed independently during repair.
- `qa:inspection`: PASS, including ownership, routes, aliases, runtime proof, engineering drift, pipeline/Qodana wiring, publish guards, and OpenAPI dump.
- `release:verify`: PASS end-to-end, including runtime, branch wiring, controller decomposition, fixtures, fixture load, container boot, Doctrine mapping, admin surface, functional readiness, and PostgreSQL-matrix smoke checks.

### Repairs completed during verification

- Added the direct `Administering` dependency after confirming `ApplicatingFrameworkConfigService` consumes its config-tool services and value contracts.
- Corrected Doctrine identity ownership: `Application` no longer duplicates Objecting's embedded `slug`; slug reads/writes and repository lookup now use `objectIdentity.slug`.
- Made the test user-data database self-contained on SQLite, matching the CI environment that installs SQLite without provisioning a PostgreSQL service; dev/production PostgreSQL policy remains unchanged.
- Registered Nelmio for dev/test and replaced the obsolete Swagger UI XML resource route with the current controller route; README now records `/api/applicating/doc` as the dev/test API documentation UI.
- Updated PHPMD Composer scripts for the installed PHP 8.4-capable 3.x CLI and reduced the two reported command complexity hotspots without weakening quality thresholds.
- Canon038 migration now uses the canonical `application_` YAML subject prefix while preserving conventional framework/vendor filenames.

### RC disposition

- RC-critical implementation and verification are complete in the working tree.
- Post-RC growth remains intentionally separate: catalog scorecards, richer environment promotion UX, self-service templates, and expanded cross-runtime observability.
- Remaining acceptance action: Git review/integration and push of the verified working tree.
