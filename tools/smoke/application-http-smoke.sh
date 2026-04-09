#!/usr/bin/env bash
set -euo pipefail

base_url="${1:-}"
if [[ -z "${base_url}" ]]; then
  echo "Usage: $0 <base-url>" >&2
  exit 1
fi

endpoints=(/health /ready /login)
status="complete"
for endpoint in "${endpoints[@]}"; do
  code="$(curl -k -s -o /dev/null -w '%{http_code}' "${base_url}${endpoint}")"
  if [[ "${code}" != 2* && "${code}" != 3* ]]; then
    status="incomplete"
  fi
done

printf '{"tool":"application-http-smoke","status":"%s","baseUrl":"%s"}
' "${status}" "${base_url}"
