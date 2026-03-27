# Cookbook (M4)
## 1) Generate 2048 variants
python3 script/variant_scale_v2/generate_variants_v2.py data/product-template.json data/product-m4.ndjson 2048
## 2) Validate + index + report
python3 script/validate/validate_variants.py data/product-m4.ndjson
python3 script/indexer/build_index.py data/product-m4.ndjson data/index.json
python3 script/report/generate_report.py data/product-m4.ndjson data/index.json data/summary.json
## 3) Publish webhook event (signed)
python3 script/webhook/publish_event.py product.changed data/product-m4.ndjson data/webhook-product.changed.json dev-secret
