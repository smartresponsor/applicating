#!/usr/bin/env bash
set -euo pipefail
echo "[smoke] Product M1: ER+API freeze presence"
test -f "./canon/schema/product.schema.json"
test -f "./canon/contract/product.openapi.json"
echo "[smoke] OK"
