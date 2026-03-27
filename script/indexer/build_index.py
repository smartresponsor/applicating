#!/usr/bin/env python3
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
import json, sys, hashlib
def main(inp, outp):
    idx=[]; total=0
    with open(inp,"r",encoding="utf-8") as f:
        for line in f:
            if not line.strip(): continue
            p=json.loads(line)
            for v in p.get("variant", []):
                total += 1
                digest=hashlib.sha256((p["id"]+v["sku"]).encode()).hexdigest()[:12]
                if len(idx) < 50:  # sample
                    idx.append({"id":p["id"],"sku":v["sku"],"slug":p.get("slug",""),"digest":digest})
    with open(outp,"w",encoding="utf-8") as g:
        json.dump({"count":total, "index": idx}, g, indent=2)
    print("[index] entries={} -> {}".format(total, outp))
if __name__=="__main__":
    if len(sys.argv) < 3:
        print("Usage: build_index.py <input.ndjson> <index.json>"); sys.exit(1)
    main(sys.argv[1], sys.argv[2])
