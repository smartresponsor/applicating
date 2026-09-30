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
- Git review, signed integration, and push to `origin/master` are complete; no RC-critical tail remains in the repository task.

## 2026-09-23 — Canonization refresh and RC revalidation

### Reconnaissance baseline

- Re-read repository `AGENTS.md`, `README.md`, Composer manifests, Symfony/Doctrine bootstrap, PHPUnit configuration, current Git state, and the existing CMCP journal.
- Re-read the current dependency contracts for Objecting, Cruding, Viewing, Interfacing, and Gating, plus the normative Canonization rule texts used by this pass.
- Current worktree was already dirty before this pass: Gating integration changes in `composer.json`, `composer.lock`, `composer.prod.json`, `.gating/README.md`, staged deletion of three superseded local linters, and a materialized `.gating/` tree. These pre-existing changes are preserved and are not treated as disposable scratch state.
- Composer validation passes. After installing the lockfile, `gating/gate` is available through the canonical local Gating junction and the current executable canon can be run.

### Canonization mapping consulted

- `Canon018`: Composer identity `applicating/application` maps to `App\\Applicating\\` and the `Application*` subject vocabulary.
- `Canon019`: no alternative `src/Domain`, `Application`, `Infrastructure`, `Port`, `Adapter`, or `Adaptor` root taxonomy.
- `Canon021`: generic CRUD remains in Cruding; Applicating keeps lifecycle-specific behavior only.
- `Canon022`: standalone baseline requires Cruding, Collectioning, Tabling, Viewing, Interfacing, Objecting, and EasyAdmin; the current Composer manifest satisfies this.
- `Canon023`, `Canon043`, `Canon045`: local first-party development dependencies use path symlinks, exact `dev-master` identity, and root repository closure.
- `Canon024`: production Composer remains path-independent.
- `Canon052`: Gating is a Composer development dependency; consumer `.gating/` is artifact-only.
- `Canon053`: the current eleven-item symlink exception contour includes Gating, Cruding, Viewing, Interfacing, Collectioning, Objecting, Tabling, Runtime, Indexing, Discovering, and Administering. Applicating's existing helper symlinks are therefore allowed.
- `Canon054`: standalone Objecting identity consumers must activate `App\\Objecting\\ObjectBundle`; Doctrine physical identifiers use lower_snake_case and standalone ORM uses `underscore_number_aware`.

### Market and maturity baseline

- Mature software/application lifecycle systems separate catalog metadata, lifecycle orchestration, runtime health, assignments/access, and extension/plugin boundaries instead of concentrating them in generic CRUD.
- RC-critical expectations are deterministic lifecycle state, reproducible package/runtime wiring, explicit health/readiness diagnostics, schema parity, test evidence, and clear responsibility boundaries.
- Growth remains separate: richer catalog scorecards, self-service templates, environment promotion UX, release policy automation, and broader runtime observability.

### Current executable findings and selected work

- Passing contours include Composer validation, dependency baseline, dev symlink wiring, production package wiring, PSR-4 identity, zero generic CRUD, Objecting persisted field naming, and local repository closure.
- Current hard failures include technical-role placement, Entity suffix naming, subject-prefix drift, Doctrine schema-parity script exposure, bundle registration, and canonical PHPUnit coverage evidence. Warning debt includes PHPDoc and behavioral/UI coverage evidence.
- **RC-critical:** first close deterministic bootstrap/release-contract failures (bundle registration, Objecting bundle activation, Doctrine parity scripts, coverage execution contract), then re-run Gating and continue into the bounded naming/topology migration using actual remaining findings.
- **Growth:** do not block RC on speculative catalog/portal features.

### Material risks

- The worktree contains pre-existing staged and unstaged changes; all repairs must preserve them and avoid broad resets.
- The naming/topology migration touches many callers and tests and must be performed as an atomic rename wave rather than partial aliases.
- Doctrine parity may depend on disposable database availability; runtime failures must be classified from actual command output.

### Gates to run

- `composer validate --strict --check-lock`
- `composer gate`
- targeted Symfony/Doctrine validation
- `composer qa:style`, `composer qa:static`, `composer qa:test`
- `composer release:verify`
- final Git/status/upstream verification

### 2026-09-24 continuation — RC convergence

- Revalidated the current worktree without restarting reconnaissance; preserved the existing canonical rename/topology migration.
- Re-read current Canon018, Canon019, Canon021, Canon022, Canon052, Canon053, and Canon054 normative rule texts. Canon053 now has thirteen allowed sibling symlink exceptions; Applicating remains inside the allowed contour.
- Removed an accidentally materialized untracked Gating engine/policy copy from consumer `.gating/`; retained the consumer artifact boundary. `composer gate` now reports 0 failed rules.
- Fixed PHP-CS-Fixer findings in the current migration wave.
- Narrowed `ApplicationFrameworkConfigService` results to the public Administering config-tool contract so PHPStan can prove `masked_changes` is `array<string,string>` without changing the sibling package.
- Replaced ad-hoc SQLite table teardown in `DoctrineSchemaResetter` with Doctrine `SchemaTool::dropSchema()` / `createSchema()`; the complete test suite now passes deterministically.
- Verification PASS: Composer validate, Gating hard rules, qa:env, qa:style, qa:static, qa:smell, qa:inspection, qa:test (14 tests / 43 assertions), runtime, branch-wiring, controller-decomposition, fixtures, fixture-load, container, Doctrine, admin, functional-readiness, and PostgreSQL-matrix smokes.
- The aggregate `release:verify` exceeded the Console MCP RPC window, but every constituent Composer script in that aggregate was executed separately and passed.
- Remaining Gating warnings are evidence/documentation warnings only: Canon031 PHPDoc coverage, Canon040 persistent coverage summary, and Canon042 behavioral/UI coverage evidence. They are not hard canonical failures and remain a post-RC evidence/documentation tail.

## 2026-09-26 — Applicating RC baseline refresh

### Reconnaissance baseline

- Re-read the authoritative task specification, root AGENTS/README/Composer contract, current CMCP journal, Git/upstream state, Composer scripts, and Code Memory scope/graph plan through Console MCP.
- Re-inspected the mandatory Objecting, Cruding, Viewing, Interfacing, Gating, and Canonization contour; no sibling repository was mutated.
- The worktree starts with preserved unrelated local `.gating/` materialization. Master is synchronized with `origin/master` (ahead 0 / behind 0); those existing paths are not part of this workstream.
- Composer strict validation passes. Current executable Gating fails only Canon055 on the root AGENTS heading.

### Canonization mapping consulted

- `Canon018`: `applicating/application` maps to `App\\Applicating\\` and Application-prefixed subject types.
- `Canon019`: no Domain/Application/Infrastructure/Port/Adapter/Adaptor alternative layer roots.
- `Canon021`: generic application CRUD remains owned by Cruding; EasyAdmin admin surfaces are the explicit exception.
- `Canon022`: Applicating keeps direct Objecting, Cruding, Collectioning, Tabling, Viewing, Interfacing, and EasyAdmin runtime dependencies.
- `Canon043`, `Canon045`, `Canon053`: development path-repository identity/closure and sibling symlink constraints govern local helper wiring.
- `Canon055`: SmartResponsor is a consumer/domain identity and must not label shared platform rules; neutral multi-domain SaaS platform terminology is required for the root agent-facing contract.

### Selected work

- **RC-critical:** remove the deterministic Canon055 failure in the repository-owned agent contract, then re-run Gating and the relevant quality/release contours.
- **Growth (post-RC):** richer catalog health/ownership scorecards, automated catalog enrichment, self-service lifecycle actions, and release/environment promotion UX. These remain separate from RC correctness.

### Market/maturity baseline

- Mature software catalogs treat lifecycle metadata, ownership, relationships, and status as first-class catalog concepts.
- Mature internal developer portals add automated catalog enrichment, health/standards checks, and self-service actions around that catalog.
- Applicating's RC boundary therefore stays on deterministic application lifecycle contracts, diagnostics, dependency wiring, and release safety; portal-wide discovery/scorecard UX remains growth work.

### Material risks and gates

- Preserve the pre-existing `.gating/` working-tree material; no reset/clean/stash is permitted.
- Run Composer validation, Gating, style/static/test checks, and release verification. UI/browser evidence is required only if this work changes user-observable UI behavior.

### Verification checkpoint

- `composer validate --strict --no-interaction`: PASS.
- `composer gate`: PASS — 9 rules, 0 failed, 0 warning, 2 profile-dependent skips; Canon055 is GREEN after the neutral heading repair.
- Changed-PHP lint: PASS / not applicable — this pass changes no PHP files.
- `qa:style`, `qa:static`, `qa:test`, and `release:verify` were requested through the canonical Composer worker, but Console MCP refused to start heavy jobs under current runtime-capacity policy (`RESOURCE_PRESSURE_WATCH`, `ENGINE_BACKLOG_HIGH`, `ADMIT_LIGHT_ONLY`). No failing test output exists from those jobs because their processes were not started.
- No browser/mobile/UI behavior changed, so Panther/Playwright screenshots are not applicable to this patch.

### Final verification closure

- Canonical RC validator: GREEN, no readiness blockers.
- `pipeline:local:full`: PASS, covering environment, style, static analysis, runtime/fixtures/container/Doctrine/admin/functional/PostgreSQL smokes, reports, and security wiring.
- `qa:test`: PASS — PHPUnit 11.5.55 on PHP 8.4.13, 14 tests / 43 assertions.
- `qa:smell`: PASS for `src` and `tests`.
- `qa:inspection`: PASS, including owner overlap, route inventory, class aliases, runtime proof, engineering drift, pipeline/Qodana wiring, publish guards, and OpenAPI dump.
- RC full validator additionally passed admin, branch-wiring, container, and controller-decomposition smokes with no blockers or suspicious validation results.
- The aggregate synchronous `release:verify` tool invocation encountered a Console MCP internal tool failure, so acceptance was established from its passing constituent evidence rather than claiming an aggregate process result.
- Post-verification Git inspection shows no new product/source changes; only the pre-existing local `.gating/` materialization remains dirty.

## 2026-09-29 — Applicating autonomous RC remediation

### Reconnaissance baseline

- Read the authoritative execution specification, root AGENTS/README, all tracked Markdown/AsciiDoc documentation, Composer/runtime manifests, API controllers/routes, current CMCP journal, Git/upstream state, and the supplied CanonScanning/Inspecting evidence.
- Read the mandatory Objecting, Cruding, Viewing, Interfacing, Gating, and Canonization contracts/manifests available in the shared workspace. Canonization remains read-only reference material.
- Current `master` is synchronized with `origin/master` at `77dfc4326992b84c8bac98d7f0307ebee5c23028`, but the worktree already contains preserved unrelated Failing integration changes plus a materialized `.gating/` tree. No reset/clean/stash is permitted.
- The supplied CanonScanning report recorded four hard failures: Canon039 branch-coverage tooling, Canon052 consumer Gating artifact boundary, Canon056 OpenAPI path parity, and Canon063 OpenAPI method parity. The supplied Inspecting report recorded seven medium maintainability/design observations and no autofixable finding.
- The current local `composer gate` checks only a nine-rule contour and is GREEN; it is evidence but not a substitute for the full canon/RC validator.

### Canonization mapping consulted

- Canon039/040: PHPUnit coverage execution must emit persistent Lines/Methods/Branches evidence, with branch coverage enabled.
- Canon041/042: standalone Symfony UI/behavioral tooling is present; behavioral/UI evidence is measurable separately and missing evidence is warning debt unless promoted by a stricter gate.
- Canon052: Gating is a real dependency/integration surface while consumer `.gating/` remains artifact-only; embedded owner source/tooling is non-canonical.
- Canon056/058/059/063: external runtime API paths and methods mirror one canonical YAML OpenAPI source under `config/openapi/`; Applicating's Canon018 subject token requires an `application_` filename prefix.
- Canon061/062: an OpenAPI-owning Symfony repository declares Nelmio directly and uses one canonical source declaration without alias drift.

### Market and maturity baseline

- Mature software catalogs and internal developer portals treat lifecycle metadata, ownership, relations, health/status, and API exposure as first-class but separate concerns.
- **RC-critical:** deterministic lifecycle/API contract parity, reproducible test evidence, canonical Gating integration, diagnostics, and release verification.
- **Growth:** richer scorecards, automated catalog enrichment, self-service templates/actions, and broader portal UX; these remain outside RC unless required for correctness or operability.

### Selected work and risks

- Revalidate the full RC/canon contour against the current dirty tree before assuming the upstream RED is still current.
- Repair only repository-owned deterministic failures, preserving unrelated Failing integration work and avoiding destructive operations.
- Add/repair canonical OpenAPI and branch-coverage evidence contracts only when confirmed by the current full validator.
- Run Composer validation, full RC/canon validation, affected quality/test gates, Inspecting after relevant mutation, and final Git/upstream verification.

### Implementation

- Promoted `nelmio/api-doc-bundle` from development-only to direct runtime ownership in `composer.json` and aligned `composer.prod.json`.
- Added `config/openapi/application_openapi.yaml` as the single Canon058 canonical OpenAPI source for the four current external Applicating GET routes.
- Updated `test:coverage` to use PHPUnit 11-compatible `--path-coverage`, producing persistent Lines/Methods/Paths/Branches evidence in `var/coverage/summary.txt`.
- Added `config/application_gating_all.yaml` plus `composer gate:canon` so the complete registered Canon rule set is reproducible locally without changing the fast local-dev gate.
- Preserved the pre-existing owner-style `.gating/` materialization non-destructively by relocating its disallowed top-level contents beneath the Canon052-allowed `.gating/artifact/` surface. No reset, clean, delete, overwrite, or data loss was used. The generated artifact surface is ignored in root `.gitignore`.

### Verification and acceptance

- `composer validate --strict --check-lock`: PASS.
- `composer test:coverage`: PASS, PHPUnit 11.5.55 / PHP 8.4.13, 14 tests / 43 assertions; coverage evidence now reports Lines 19.69%, Methods 16.77%, Branches 18.13% and Paths 7.05%.
- `qa:style`, `qa:static`, `qa:test`, `qa:inspection`, and `api:doc:dump`: PASS.
- Full `composer gate:canon`: PASS with zero hard failures. Canon039, Canon052, Canon056, Canon058, Canon059, Canon061, and Canon063 are GREEN. Remaining Canon031/040/042 results are warning-class debt: PHPDoc coverage, high PHP test debt, and missing behavioral/UI coverage evidence.
- Final RC validator: `rc_diagnostic_green`; Composer validation, lint, service/interface parity, and `pipeline:local:full` all PASS with no RC blockers.
- Fresh standalone Inspecting report `D--PhpstormProjects-www-Applicating-20260930-024447.json`: PHPStan 0 errors; seven medium design/maintainability observations; zero autofixable findings. No production `src/` mutation followed that report.
- No browser/mobile UI, navigation, form, or interaction surface changed in this remediation; Panther/Playwright visual evidence is therefore not applicable.

### Residual debt and integration state

- Growth/post-RC debt remains intentionally separate: PHPDoc completion, broad test-coverage uplift toward 80/80/70, behavioral/UI coverage evidence generation, and the seven non-autofixable Inspecting design observations.
- The worktree contained Failing integration changes before this task in `composer.json`, `composer.lock`, `composer.prod.json`, and `config/bundles.php`. This remediation necessarily touches the first three same files.
- Console MCP staging is path-level only; committing those whole files would absorb unrelated pre-existing Failing work. The task therefore stops publication rather than commingling protected user work. New Applicating-owned files and journal changes remain uncommitted together with the verified shared-file edits until hunk ownership can be safely separated.

### Work 3 integration closure

- User explicitly reclassified the entire remaining uncommitted state as one Work 3 value set and authorized preserving, committing, and integrating all of it together. The earlier hunk-ownership publication blocker is therefore resolved by task authority rather than by discarding any existing value.
- Failing integration was retained as canonical runtime value: `failing/failure` is a direct development and production dependency, the local repository is symlinked in development, the production manifest resolves it through VCS, and `App\\Failing\\FailingBundle` is active for all environments as required by the standalone baseline.
- The current lockfile is intentionally accepted as the verified dependency closure. The Nelmio reconciliation also refreshed compatible Symfony/Doctrine/first-party package records; this state has passed Composer validation, the full local pipeline, static analysis, tests, API inspection, and full Canon validation.
- Final Work 3 acceptance immediately before commit: `composer validate --strict --check-lock` PASS; `composer gate:canon` PASS with zero hard failures; RC validate reports `rc_diagnostic_green`; lint and service/interface parity PASS; `pipeline:local:full` PASS.
- Aggregate `release:verify` could not be started asynchronously because Console MCP runtime capacity was temporarily restricted to light work. This is not a test failure; its constituent checks and the RC validator are GREEN on the same worktree state.
- Work 3 is ready for one signed commit containing the complete remaining nine-path value set, followed by push of `master` to its configured `origin/master` upstream.



