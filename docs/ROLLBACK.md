# Applicating rollback strategy

## Scope
This rollback note covers the active Applicating runtime only. Archived catalog/product/platform experiments are outside the live rollback path.

## Golden rule
Prefer **roll forward or redeploy previous known-good revision**. Do not run destructive down-migrations automatically in production.

## When to rollback
Rollback is warranted when one or more of these happen after deployment:
- `/health` fails
- `/ready` stays `not_ready`
- login page stops responding
- admin API protection is broken
- publish or tenant assignment flows regress in smoke or operator checks

## Component-level rollback sequence
1. Stop or reduce traffic to the failing revision.
2. Redeploy the previous known-good chart/image revision.
3. Re-run post-deploy smoke against `/health`, `/ready`, `/login` and protected admin API routes.
4. Inspect logs using the `X-Request-Id` correlation header for the failing requests.
5. If a schema migration was part of the rollout, keep the database on the latest safe additive schema unless a manual DBA-approved rollback plan exists.

## Data safety notes
- Current Doctrine migrations should be treated as forward-only unless explicitly reviewed for safe reversal.
- Demo fixture loading is for local/dev bootstrap only and must not be part of production rollback.
- Persistent admin users live in `application_user`; rollback must preserve those rows.

## Release operator checklist
- know the previous good revision identifier
- know whether the release included Doctrine migrations
- confirm smoke passes after redeploy
- capture request IDs and timestamps for any incident report
