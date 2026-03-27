#!/usr/bin/env python3
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
import json, sys
def main(outp):
    sample = {"id":"p-999","slug":"sample","title":"Sample","variant":[{"sku":"SAMPLE","option":[{"code":"size","value":"M"}]}],
              "price": {"amount": 1.0, "currency": "USD"}}
    with open(outp, "w", encoding="utf-8") as g:
        g.write(json.dumps(sample) + "\n")
    print("[export] OK ->", outp)
if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Usage: export_ndjson.py <output.ndjson>"); sys.exit(1)
    main(sys.argv[1])
