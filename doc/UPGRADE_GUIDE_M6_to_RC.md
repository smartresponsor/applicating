# Upgrade Guide — M6 → RC
1) Review api-freeze.json. Ensure clients use v1 endpoints from GA (M4/M5).
2) Apply alert rules (Prometheus) and import Grafana dashboard (rc-overview.json).
3) Dry-run OpenAPI/Schema diffs using rc tools with your current artifacts:
   - script/rc/openapi_diff.py <old.json> <new.json> data/openapi-diff.json
   - script/rc/schema_diff.py <old_dir> <new_dir> data/schema-diff.json
4) Check deprecation windows; plan migrations before GA cut.
5) Tag release: v1.0.0-rc.1.
