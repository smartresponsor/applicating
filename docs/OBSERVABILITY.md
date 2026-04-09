# Applicating observability baseline

## Current baseline
The active runtime currently exposes a lightweight operational baseline:
- `/health` for liveness
- `/ready` for readiness
- `X-Request-Id` response header for request correlation
- Monolog-backed application logging

## What is active today
- smoke-postdeploy checks can probe `/health` and `/ready`
- request correlation can be used to trace a single failing request through logs
- readiness logic is application-centric and tied to this runtime rather than to archived platform experiments

## What is intentionally not claimed
The active contour does **not** currently claim production-grade:
- OpenTelemetry trace export
- Jaeger/Loki wiring
- cross-component platform dashboards inherited from older experiments
- federation observability

Those older observability experiments were archived out of the active contour because they did not describe the current Applicating runtime truth.

## Next practical step
When metrics or tracing are added, they should be introduced under Applicating-specific names, dashboards and alerts rather than through inherited catalog, mesh or product terminology.
