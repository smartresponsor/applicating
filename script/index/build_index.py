#!/usr/bin/env python3
import json, sys, hashlib
def main(inp, outp):
    idx=[]; total=0
    with open(inp,"r",encoding="utf-8") as f:
        for line in f:
            if not line.strip(): continue
            p=json.loads(line); total += 1
            for v in p.get("variant", []):
                digest=hashlib.sha256((p["id"]+v["sku"]).encode()).hexdigest()[:12]
                idx.append({"id":p["id"],"sku":v["sku"],"slug":p["slug"],"digest":digest})
    with open(outp,"w",encoding="utf-8") as g: json.dump({"count":len(idx),"index":idx[:50]}, g, indent=2)
    print("[index] ->", outp, "entries", len(idx))
if __name__=="__main__":
    if len(sys.argv)<2: print("Usage: build_index.py <in.ndjson> <out.json>"); sys.exit(1)
    main(sys.argv[1], sys.argv[2] if len(sys.argv)>2 else "index.json")
