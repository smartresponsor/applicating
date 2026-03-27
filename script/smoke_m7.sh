#!/usr/bin/env bash
set -euo pipefail
echo "[smoke] M7 RC"
test -f VERSION
test -f api-freeze.json
test -f canon/policy/rc-policy.json
test -f alerts/prometheus/alerts-m7-rc.yml
test -f dashboards/grafana/rc-overview.json
python3 script/rc/openapi_diff.py canon/contract-old.json canon/contract-new.json data/openapi-diff.json || true
python3 script/rc/schema_diff.py canon/schema-old canon/schema-new data/schema-diff.json || true
echo "[smoke] OK"
