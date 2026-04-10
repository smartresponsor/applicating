# Applicating / Application

Symfony-oriented application lifecycle component centered on `Application` as a software ecosystem unit, not a retail product.

## Scope

- application listing and lifecycle metadata
- manifest management and release/version publication
- tenant assignment, installation and enable/disable lifecycle
- runtime, sandbox, billing and governance hooks
- admin UI, JSON admin API and Symfony Console operations
- diagnostics, reports and demo fixtures

Retail product CRM, goods-management and older Smartpolicy/Product-suite experiments are no longer part of the active operational contour. Historical residue was moved under `archive/repo-drift/` so it cannot keep defining live workflows, deployment paths or operational docs.

## Active runtime contour

Authoritative runtime and delivery areas:
- `src/`
- `config/`
- `templates/`
- `tests/`
- `public/`
- `tools/`
- `helm/applicating/`
- `k8s/sealedsecrets/applicating-secrets.yaml`
- `.github/workflows/` curated Applicating workflows
- `docs/` curated Applicating documentation

Historical or non-active material is retained only for reference under `archive/repo-drift/`.

## Runtime

1. Install dependencies:
   ```bash
   composer install
   ```
2. Create schema:
   ```bash
   php bin/console doctrine:migrations:migrate --no-interaction
   ```
3. Load demo data:
   ```bash
   php bin/console applicating:fixtures:load-demo
   ```
4. Start the app:
   ```bash
   symfony server:start -d
   ```
   Fallback:
   ```bash
   php -S 127.0.0.1:8000 -t public
   ```

Login page: `/login`

Demo users (created by `applicating:fixtures:load-demo`):
- `admin / admin`
- `manager / manager`
- `viewer / viewer`

The development environment stores hashed demo credentials in `.env`; the test environment overrides hashing to keep functional authentication tests readable.

## Main flows

- `/admin/applications` for application listing and management
- `/admin/applications/{id}` for releases, manifests and tenant assignments
- `/api/admin/applications` and `/api/admin/v1/applications` for admin JSON vocabulary centered on `Application`
- `/health`, `/ready` and `/login` for runtime liveness/readiness/access checks
- `php bin/console applicating:*` for operational workflows

## Quality gate

Primary local pipeline:

```bash
composer qa:full
composer qa:inspection
composer pipeline:local:full
```

HTTP smoke against a running instance:

```bash
bash tools/smoke/application-http-smoke.sh http://127.0.0.1:8000
composer smoke:admin
composer smoke:functional-readiness
composer smoke:postgres-matrix
composer smoke:branch-wiring
composer smoke:controller-decomposition
```

Optional browser E2E path:

```bash
npm install
composer test:playwright
```

Inspection reports are written to `report/inspection/`; pipeline/runtime logs remain under `var/`.

The active Qodana lane excludes `archive/repo-drift`, and the `PhpGetterAndSetterCanBeReplacedWithPropertyHooksInspection` is disabled for now as a deliberate owner decision until a Doctrine/property-hooks migration policy is approved.
The GitHub workflow lives in `.github/workflows/qodana.yml` and runs the same configured lane on pull requests and pushes to `master`. The local `composer report:qodana-wiring` report checks that the workflow and root `qodana.yaml` stay wired together.

The runtime proof contour is split into `composer smoke:runtime`, `composer smoke:container`, `composer smoke:doctrine`, `composer smoke:fixture-load`, `composer smoke:admin`, and `composer smoke:functional-readiness` so environment readiness stays distinct from code regressions.


GitHub `qa.yml` now runs the canonical `composer qa:full` path on pull requests and pushes to `master`, and publishes `report/inspection/` as the `applicating-inspection-reports` artifact. `ci-smoke.yml`, `security-check.yml`, `build.yml`, `playwright.yml` and `cd.yml` are also part of the curated active contour and are expected to stay wired to the Applicating runtime.
