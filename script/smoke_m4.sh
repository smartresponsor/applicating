#!/usr/bin/env bash
set -euo pipefail
echo "[smoke] M4 GA"
python3 script/variant_scale_v2/generate_variants_v2.py data/product-template.json data/product-m4.ndjson 2048
python3 script/validate/validate_variants.py data/product-m4.ndjson
python3 script/indexer/build_index.py data/product-m4.ndjson data/index.json
python3 script/report/generate_report.py data/product-m4.ndjson data/index.json data/summary.json
python3 script/webhook/publish_event.py product.changed data/product-m4.ndjson data/webhook-product.changed.json dev-secret
test -f data/summary.json
test -f data/webhook-product.changed.json
echo "[smoke] OK"
