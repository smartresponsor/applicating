# Applicating wave 4 fixture governance map

## Focus

The active fixture layer currently appears to conflict with the planned publish-guard direction.

## Observed risk in `ApplicationFixtures`

The fixture flow currently combines two signals:

- some manifests are created with `governanceState = review_required`;
- the first four applications are still published automatically.

That means fixture data can currently produce published applications whose manifest governance is not approved.

## Why this matters

This is inconsistent with the next domain-quality direction already identified for `publishApplication()`:

- publish should require manifest presence;
- publish should require approved governance state;
- negative tests should fail loudly when eligibility is not met.

## Follow-up target

When the wave 3 publish-guard rewrite is applied, `ApplicationFixtures` should be aligned in one of two ways:

1. only publish fixture applications whose manifest governance is approved; or
2. keep review-required fixture applications in draft/moderation state instead of published state.
