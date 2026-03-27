#!/usr/bin/env bash
set -euo pipefail
echo "[e2e] Start"
# 1) PIM -> NDJSON
python3 script/pim/pim_export.py data/pim.ndjson
# 2) Import (dry-run)
python3 script/import/import_ndjson.py data/pim.ndjson data/import.json
# 3) Build index
python3 script/index/build_index.py data/pim.ndjson data/index.json
# 4) Search reindex
python3 script/search/search_reindex.py data/index.json data/search.json
# 5) Price compute via plugin
python3 script/plugin/plugin_loader.py '{"price": 21.0, "account":"acc-demo", "segment":"vip"}' > data/price.json
# 6) ERP sync price list
python3 script/erp/erp_sync.py config/b2b-price-list.json data/erp.json
# 7) Publish webhook
python3 script/webhook/publish.py product.changed data/pim.ndjson data/webhook.json
# 8) Summary
python3 - <<'PY'
import json,sys
with open("data/import.json") as f: imp=json.load(f)
with open("data/index.json") as f: idx=json.load(f)
with open("data/search.json") as f: si=json.load(f)
with open("data/price.json") as f: price=json.load(f)
with open("data/erp.json") as f: erp=json.load(f)
with open("data/webhook.json") as f: wh=json.load(f)
summary={"import":imp,"indexCount":idx.get("count"),"searchCount":si.get("count"),
         "computedPrice":price.get("price"),"erpRules":erp.get("rules"),"webhookType":wh.get("type")}
with open("data/summary.json","w") as g: json.dump(summary, g, indent=2)
print("[e2e] summary -> data/summary.json", summary)
PY
echo "[e2e] OK"
