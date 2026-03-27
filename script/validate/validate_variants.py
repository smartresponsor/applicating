#!/usr/bin/env python3
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
import json, sys
def key_of(v):
    return "|".join(sorted(["{}={}".format(o["code"], o["value"]) for o in v.get("option", [])]))
def main(inp):
    seen=set(); count=0
    with open(inp, "r", encoding="utf-8") as f:
        for line in f:
            if not line.strip(): continue
            p=json.loads(line)
            for v in p.get("variant", []):
                k=key_of(v)
                if k in seen:
                    print("[validate] DUP", k); sys.exit(2)
                seen.add(k); count += 1
    print("[validate] OK variants={} unique={}".format(count, len(seen)))
if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Usage: validate_variants.py <input.ndjson>"); sys.exit(1)
    main(sys.argv[1])
