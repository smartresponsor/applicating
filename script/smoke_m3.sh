#!/usr/bin/env bash
set -euo pipefail
echo "[smoke] M3 types + scale600"
python3 script/variant_scale/generate_variants.py data/product-template.json config/product-types.json data/product-m3.ndjson 650
python3 script/validate/validate_variants.py data/product-m3.ndjson
python3 script/indexer/build_index.py data/product-m3.ndjson data/index.json
python3 script/report/generate_report.py data/product-m3.ndjson data/index.json data/summary.json
python3 script/bulk/bulk_edit.py data/product-m3.ndjson data/product-m3-upd.ndjson 1.10
python3 script/bulk/diff_preview.py data/product-m3.ndjson data/product-m3-upd.ndjson data/price.diff
test -f data/summary.json
echo "[smoke] OK"
