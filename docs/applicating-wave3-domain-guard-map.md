# Applicating wave 3 domain guard map

## Focus

The active runtime is already centered on `Applicating / Application`, but publication still looks under-guarded at the business-rule level.

## Observed gaps

### 1. Publish eligibility is not visibly guarded in the lifecycle service

`src/Service/ApplicationLifecycleService.php` publishes an application and a release directly, without an explicit visible gate for:

- presence of at least one manifest;
- governance approval state on the manifest;
- release publication eligibility;
- single-published-release semantics.

### 2. Manifest governance state is still weakly typed

`src/Entity/ApplicationManifest.php` stores `governanceState` as a plain string instead of a stronger domain type.

### 3. Integration coverage is still mostly happy-path

`tests/Integration/ApplicationLifecycleServiceTest.php` covers create/release/manifest/publish/assign/suspend, but does not clearly cover negative publication cases.

## Recommended direct code wave

1. introduce an application publication guard in the lifecycle service;
2. type or constrain manifest governance state more strongly;
3. add negative integration tests for publishing without manifest or without publish-ready governance;
4. add publication-state invariants for release publication.
