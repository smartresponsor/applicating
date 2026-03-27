#!/usr/bin/env bash
set -euo pipefail
echo "[smoke] M5 competitive"
# plugin: compute price for VIP
json='{"price": 100.0, "account": "acc-100", "segment": "vip"}'
python3 plugin/plugin_loader.py plugin on_price_compute "$json" > /tmp/price.json
cat /tmp/price.json
# connector: PIM export -> Search index
python3 connector/pim/pim_export_stub.py data/pim-out.ndjson
python3 connector/search/search_reindex_stub.py data/pim-out.ndjson data/search-index.json
# ERP: sync B2B price list
python3 connector/erp/erp_price_sync_stub.py config/b2b-price-list.json data/erp-sync.json
# audit + pii
python3 security/audit_logger.py bot deploy product '{"id":"p-1","slug":"pro-tee-ga"}' data/audit.log
echo 'contact: user@example.com' > data/sample.txt
python3 security/pii_scan.py data/sample.txt data/pii.json
# cache demo
python3 cache/edge_cache.py
# xregion replica
python3 replication/xregion_replica.py data/pim-out.ndjson data/replica
echo "[smoke] OK"
