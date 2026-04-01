# Applicating wave 3 patch manifest

This manifest records the direct code edits for the next domain-quality wave around publication guards.

## 1. `src/Service/ApplicationLifecycleService.php`

Strengthen `publishApplication()` with explicit preconditions:

- the release must belong to the application being published;
- the release must not already be published;
- the application must have at least one manifest;
- at least one attached manifest must be governance-approved before publication;
- publication should fail loudly when eligibility is not met.

Add a dedicated private guard method such as `assertApplicationPublishable()` to keep the rule explicit and testable.

## 2. `tests/Integration/ApplicationLifecycleServiceTest.php`

Keep the existing happy-path lifecycle flow and add negative integration coverage for:

- publishing without any manifest;
- publishing with a non-approved governance state;
- publishing a release that does not belong to the application;
- publishing an already-published release.

## 3. `src/Entity/ApplicationManifest.php`

Harden governance semantics so that `governanceState` is no longer a weak arbitrary string in the active model. The preferred direction is a dedicated enum or other constrained domain type.

## 4. `src/DTO/Application/ApplicationManifestData.php`

Keep form-level validation aligned with the stronger governance semantics so admin forms and CLI payloads cannot silently drift away from the domain rule.
