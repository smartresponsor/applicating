#!/usr/bin/env python3
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
import json, sys, re
EMAIL=re.compile(r"[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}")
def main(inp, outp):
    findings=[]
    with open(inp,"r",encoding="utf-8") as f:
        for i, line in enumerate(f,1):
            for m in EMAIL.findall(line):
                findings.append({"line": i, "match": m})
    with open(outp,"w",encoding="utf-8") as g: json.dump({"findings":findings}, g, indent=2)
    print("[pii] matches:", len(findings))
if __name__=="__main__":
    if len(sys.argv)<3: print("Usage: pii_scan.py <in.txt|json|ndjson> <out.json>"); sys.exit(1)
    main(sys.argv[1], sys.argv[2])
