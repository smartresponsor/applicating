# Applicating lightweight threat model

## Primary assets
- application release metadata and publish state
- manifest governance state
- tenant assignment state and access policy
- admin credentials and admin session

## Main abuse cases
1. Unauthorized access to admin API surfaces
2. Brute-force attacks against the login form
3. Duplicate or conflicting releases/manifests for the same application
4. Cross-tenant assignment confusion caused by duplicate tenant/application rows
5. Operational blind spots when a deployment is unhealthy but still receives traffic

## Current mitigations
- role-based access checks in controllers and voter paths
- login throttling on the main firewall
- persistent admin identities in the application database, with in-memory auth restricted to the test override
- unique schema constraints for release, manifest and tenant assignment invariants
- `/health` and `/ready` endpoints for smoke and readiness probes
- `X-Request-Id` correlation header for request tracing

## Remaining risks
- there is still no external OIDC/SAML connector; enterprise SSO remains a future integration step
- admin API versioning is compatibility-oriented, not contract-negotiated
- readiness and observability remain application-level and do not yet include full metrics/tracing export
