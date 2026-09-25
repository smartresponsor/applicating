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
