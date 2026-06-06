# Applicating operations map

## Runtime truth
- primary runtime code: `src/`, `config/`, `templates/`, `tests/`
- deployment chart: `helm/applicating`
- sealed secret placeholder: `k8s/sealedsecrets/applicating-secrets.yaml`
- deploy workflow: `.github/workflows/cd.yml` (Helm lint + template preflight + rollout status)
- post-deploy smoke: `.github/workflows/smoke-postdeploy.yml`
- rollback guide: `docs/ROLLBACK.md`
- archived non-active operations: `archive/repo-drift/`

## Operational checks
- liveness: `GET /health`
- readiness: `GET /ready`
- login reachability: `GET /login`
- protected admin API: `GET /api/admin/v1/application` should not be anonymously open
- correlation header: `X-Request-Id`
- login throttling: enabled for the main firewall

## Local commands
- `composer qa:env`
- `composer qa:style`
- `composer qa:static`
- `composer qa:smell`
- `composer qa:test`
- `composer qa:inspection`
- `composer qa:full`
- `composer pipeline:local:full`
- `bash tools/smoke/application-http-smoke.sh http://127.0.0.1:8000`

## Release hygiene
- schema hardening is handled by Doctrine migrations
- duplicate release version per application is rejected
- duplicate manifest identifier per application is rejected
- tenant assignment is idempotent per tenant/application pair
- only curated Applicating workflows are expected to run in active CI/CD
- `tools/release/make-release.sh` builds the curated runtime archive

## Admin identity operations
- Persistent admin identities live in the `application_user` table.
- Bootstrap demo identities with `php bin/console applicating:fixtures:load-demo`.
- Create or rotate a local administrative account with `php bin/console applicating:user:create`.
- The `test` environment keeps an isolated in-memory auth provider for deterministic browser tests and must not be used as an operational model.


## CI truth
- `qa.yml` is the canonical full-QA workflow and publishes `report/inspection/` as `applicating-inspection-reports`.
- `release.yml` runs `composer release:verify` before building `applicating-runtime.zip`.
- `release:verify` includes the admin and functional readiness smoke contour so operational drift is caught before packaging.
