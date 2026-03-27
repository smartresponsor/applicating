#!/usr/bin/env python3
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
import json, sys
def main(in_ndjson, index_json, outp):
    products=0; variants=0
    with open(in_ndjson,"r",encoding="utf-8") as f:
        for line in f:
            if not line.strip(): continue
            p=json.loads(line); products+=1; variants += len(p.get("variant", []))
    with open(index_json,"r",encoding="utf-8") as f:
        idx=json.load(f).get("count",0)
    ok2048 = variants >= 2048
    rep={"products":products,"variants":variants,"indexed":idx,"okScale2048":ok2048}
    with open(outp,"w",encoding="utf-8") as g:
        json.dump(rep, g, indent=2)
    print("[report]", rep)
if __name__=="__main__":
    if len(sys.argv) < 4:
        print("Usage: generate_report.py <input.ndjson> <index.json> <out.json>"); sys.exit(1)
    main(sys.argv[1], sys.argv[2], sys.argv[3])
