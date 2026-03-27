#!/usr/bin/env bash
set -euo pipefail
echo "[smoke] M2 import/indexer"
python3 script/import/csv_to_ndjson.py data/product.csv data/product.ndjson
python3 script/import/import_ndjson.py data/product.ndjson data/report.json
python3 script/indexer/build_index.py data/product.ndjson data/index.json
python3 script/report/generate_report.py data/report.json data/index.json data/summary.json
test -f data/summary.json
echo "[smoke] OK"
