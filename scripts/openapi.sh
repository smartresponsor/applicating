#!/usr/bin/env bash
set -euo pipefail
if [ -x ./bin/generate-openapi ]; then
  ./bin/generate-openapi
else
  echo "No bin/generate-openapi found — skipping"
fi
