#!/usr/bin/env python3
import json, sys, hashlib
def main(inp, outp):
    with open(inp,"r",encoding="utf-8") as f: data=json.load(f)
    with open(outp,"w",encoding="utf-8") as g: json.dump({"count":data.get("count",0),"digests":[e["digest"] for e in data.get("index",[])]}, g, indent=2)
    print("[search] ->", outp)
if __name__=="__main__":
    if len(sys.argv)<3: print("Usage: search_reindex.py <index.json> <out.json>"); sys.exit(1)
    main(sys.argv[1], sys.argv[2])
