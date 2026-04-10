# Applicating maintenance and CI/CD guidelines

## Local quality targets
- `composer qa:env`
- `composer qa:style`
- `composer qa:static`
- `composer qa:smell`
- `composer test`
- `composer qa:inspection`
- `composer qa:full`
- `composer pipeline:local:full`
- `composer test:playwright` (optional browser E2E path)

## Active GitHub workflows
- `build.yml` — builds the Applicating image, boots it and runs HTTP smoke against the built container
- `playwright.yml` — optional browser E2E contour for the Symfony login/admin console surface with PHP and Node setup
- `test.yml` — style, static analysis and PHPUnit on the Applicating runtime
- `qa.yml` — canonical full QA path on pull requests, pushes to `work` and manual dispatch
- `ci-precheck.yml` — verifies the active operational contour exists
- `ci-smoke.yml` — runs runtime, fixture and container smokes on pull requests and pushes to `work`
- `cd.yml` — Helm-based deployment for `helm/applicating` with lint, template preflight and rollout status gating
- `smoke-postdeploy.yml` — `/health`, `/ready`, `/login` and protected admin API smoke
- `release.yml` — runs `composer release:verify`, publishes inspection reports and builds the curated Applicating runtime archive

## Cleanup note
Historical workflow noise was moved to `archive/repo-drift/github-workflows-archive/` so the active CI/CD surface now reflects the real Applicating runtime instead of legacy platform experiments.
