# Applicating repo analysis (requested for `master`)

Date: 2026-04-10 (UTC)

## Scope and branch reality

- Local repository currently contains only branch `work`; local `master` branch is absent.
- `README.md` still documents CI behavior for pushes to `master`, so branch naming and runtime documentation are currently inconsistent.

## Executive assessment

Overall the repository already has a clear Symfony-oriented structure (DTO, Service + ServiceInterface, ValueObject, Fixtures, Twig, smoke/inspection tooling). The strongest areas are naming consistency around `Application*`, wide operational tooling, and explicit QA scripts.

Main risk areas are:

1. **Framework-version drift vs target policy**: project currently targets Symfony 7.4, while requested policy expects Symfony 8.
2. **Controller thickness**: `ApplicationAdminController` orchestrates many flows and contains repeated form handling logic, making it expensive to evolve safely.
3. **Test depth imbalance**: a lot of runtime/inspection scripts exist, but there are very few PHPUnit tests committed in-tree.
4. **Delivery branch drift**: docs and workflow language still reference `master` while active local branch is `work`.

## Findings (evidence-based)

### 1) Platform and dependency alignment

- `composer.json` pins `php` to `^8.4`, Doctrine ORM to `^3.3`, and most Symfony packages to `^7.4`.
- This is stable and coherent internally, but does not satisfy a Symfony 8-first policy.

### 2) Architecture and layering

- Canonical roots and namespace model are in place (`src/`, `App\\`), with `Service` + `ServiceInterface`, `DTO`, `ValueObject` directories.
- `ApplicationAdminController` is feature-rich and handles index/new/report/show/edit/release/manifest/publish/suspend/assign/toggle in one controller.
- This controller has duplicated POST form-processing patterns (create form → handle request → success/error flash → redirect), which indicates an extraction opportunity into dedicated action handlers or reusable internal methods.

### 3) Data and fixtures posture

- Fixtures are implemented via Doctrine Fixtures + Faker and use service-level APIs (good layering, no DQL fixture shortcuts).
- Fixture generation is mostly deterministic in shape, but Faker-driven text values remain non-deterministic content-wise unless a fixed seed is introduced.

### 4) QA and operational maturity

- Repository includes many QA and smoke surfaces (`qa:*`, `smoke:*`, `report:*`, pipeline scripts), which is a strong operational base.
- However, current environment could not execute PHPUnit because dependencies are unavailable (`vendor/bin/phpunit` missing) and `composer install` is blocked by external network restrictions (GitHub 403 tunnel error).

## Priority recommendations

### P0 (high impact, low ambiguity)

1. **Branch/documentation synchronization**
   - Decide canonical delivery branch (`master` or `work`) and align docs/workflows accordingly.
2. **Symfony 8 migration plan**
   - Introduce explicit migration checklist (package constraints, deprecations, CI matrix) before any feature expansion.

### P1 (architecture hardening)

3. **Split `ApplicationAdminController` into focused actions/controllers**
   - Keep controller endpoints thin; move repeated form + flash + redirect patterns behind dedicated application service orchestrators.
4. **Introduce explicit input validation contracts for command/API edges**
   - Preserve DTO-first approach while reducing edge-case branching in controllers.

### P2 (quality and predictability)

5. **Increase PHPUnit coverage around lifecycle service and publication logic**
   - Add regression tests for publish/suspend/assignment transitions and error paths.
6. **Deterministic fixture mode for test profiles**
   - Seed Faker in test/dev smoke contexts to reduce flakiness.

## Commands executed for this analysis

- `git status --short --branch`
- `git branch -a`
- `git remote -v`
- `git show-ref --heads`
- `rg --files`
- `rg -n "namespace App|class |interface |trait " src tests --glob '*.php'`
- `wc -l README.md docs/architecture/ACTIVE_RUNTIME.md composer.json config/services/applicating_services.yaml`
- `composer qa:env`
- `composer lint:canonical-roots`
- `composer test:functional` (failed due to missing vendor dependencies)
- `composer install --no-interaction` (failed due to GitHub network/proxy restrictions)

