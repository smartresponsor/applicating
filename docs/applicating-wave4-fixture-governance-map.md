# Applicating wave 4 fixture governance map

## Focus

The fixture layer is now aligned with the active publish-guard direction.

## Current fixture contract in `ApplicationFixtures`

The fixture flow now combines these signals intentionally:

- the first four applications receive `governanceState = approved` and are publish-eligible;
- the remaining applications receive `governanceState = review_required` and remain unpublished.

That means fixture data still preserves governance variety, but it no longer produces published applications whose manifest governance is not approved.

## Why this matters

This now matches the active domain-quality direction enforced in `publishApplication()`:

- publish requires manifest presence;
- publish requires approved governance state;
- negative tests fail loudly when eligibility is not met.

## Result

Fixture demos remain useful for runtime walkthroughs, while the demo landscape now respects the same publication eligibility rules as the service layer.
