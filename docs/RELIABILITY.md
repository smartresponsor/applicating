# Applicating reliability playbook

## Current reliability posture
Applicating is currently a single Symfony application with SQL-backed lifecycle state.

Primary reliability controls in the active contour:
- duplicate release version per application is rejected
- duplicate manifest identifier per application is rejected
- tenant assignment is idempotent per application + tenant
- `/health` and `/ready` separate liveness from readiness
- manager-scoped admin API reduces accidental write access

## Practical SLO starting point
For early RC validation, treat these as target guardrails rather than platform promises:
- availability target: 99.5%+
- readiness failures: investigate immediately
- publish/assignment command failures: investigate immediately
- duplicate-write attempts: must fail safely

## Operational checks
- post-deploy smoke hits `/health` then `/ready`
- local pipeline validates style, static analysis and tests
- Doctrine migration chain remains the source of schema truth

## Cleanup note
Older product-suite SLO rules, canary dashboards and mesh-chaos narratives were removed from the active operational contour because they belonged to earlier platform experiments, not to the current Applicating runtime.


## Delivery guardrails
- `build.yml` must boot the built image and pass `tools/smoke/application-http-smoke.sh`.
- local proof lane should keep `composer smoke:admin` and `composer smoke:functional-readiness` green alongside the runtime and Doctrine smokes.
- `cd.yml` must lint and template the Helm chart before upgrade and wait for rollout status after deployment.
- `security-check.yml` must keep persistent user provider, `ApplicationUserChecker` and manager-scoped admin API rules intact.
