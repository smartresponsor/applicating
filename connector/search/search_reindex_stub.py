#!/usr/bin/env python3
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
import json, sys, hashlib
def main(inp, outp):
    # reindex sample into a tiny hashed index
    count=0; idx=[]
    with open(inp,"r",encoding="utf-8") as f:
        for line in f:
            if not line.strip(): continue
            p=json.loads(line); count+=1
            digest=hashlib.sha1(p.get("slug","").encode()).hexdigest()[:10]
            idx.append({"id":p.get("id"), "slug":p.get("slug"), "digest":digest})
    with open(outp,"w",encoding="utf-8") as g: json.dump({"count":count,"index":idx}, g, indent=2)
    print("[search] indexed entries:", count)
if __name__=="__main__":
    if len(sys.argv)<3: print("Usage: search_reindex_stub.py <in.ndjson> <out.json>"); sys.exit(1)
    main(sys.argv[1], sys.argv[2])
