#!/usr/bin/env python3
import json, sys
def main(inp, report):
    prod=0; var=0
    with open(inp,"r",encoding="utf-8") as f:
        for line in f:
            if not line.strip(): continue
            p=json.loads(line); prod+=1; var += len(p.get("variant",[]))
    out={"importedProducts":prod,"importedVariants":var,"dryRun":True}
    with open(report,"w",encoding="utf-8") as g: json.dump(out,g,indent=2)
    print("[import] ->", report, out)
if __name__=="__main__":
    if len(sys.argv)<3: print("Usage: import_ndjson.py <in.ndjson> <report.json>"); sys.exit(1)
    main(sys.argv[1], sys.argv[2])
