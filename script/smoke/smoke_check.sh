#!/usr/bin/env bash
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

set -euo pipefail
echo "[smoke] Product M0 canon files check"
test -f "./canon/schema/product.schema.json"
test -f "./canon/contract/product.openapi.json"
echo "[smoke] OK"
