# Category local pipeline

PowerShell and shell wrappers to run the local Cataloging QA pipeline without fail-fast behavior.

## PowerShell

```powershell
powershell -ExecutionPolicy Bypass -File tools/ci/run-category-local-pipeline.ps1
```

Optional switches:

```powershell
powershell -ExecutionPolicy Bypass -File tools/ci/run-category-local-pipeline.ps1 -IncludeSmokes -IncludeReports
powershell -ExecutionPolicy Bypass -File tools/ci/run-category-local-pipeline.ps1 -FailOnErrors
```

## Output

Each run writes to `report/pipeline/<timestamp>/`:

- `summary.txt`
- `summary.md`
- `summary.json`
- `logs/*.log`

The pipeline now distinguishes `passed`, `failed`, `missing-artifact`, `misconfigured`, and `environment` so the report is easier to forward as evidence.

The smoke and report scripts are backed by live PHP entry points inside `tools/qa`, `tools/smoke`, and `tools/inspection` rather than silent skips.
