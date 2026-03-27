#!/usr/bin/env python3
import json, sys
def main(outp):
    rec={"id":"p-m6","slug":"pro-tee-m6","title":"Pro Tee M6","variant":[{"sku":"M6-S-BLACK","option":[{"code":"size","value":"S"},{"code":"color","value":"black"}]}],"price":{"amount":21.0,"currency":"USD"}}
    with open(outp,"w",encoding="utf-8") as g: g.write(json.dumps(rec)+"\n")
    print("[pim] ->", outp)
if __name__=="__main__":
    if len(sys.argv)<2: print("Usage: pim_export.py <out.ndjson>"); sys.exit(1)
    main(sys.argv[1])
