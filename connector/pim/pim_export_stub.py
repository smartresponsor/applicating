#!/usr/bin/env python3
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
import json, sys
def main(outp):
    # produce a minimal PIM export sample
    rec={"id":"p-pim-1","slug":"pim-hoodie","title":"PIM Hoodie","variant":[{"sku":"PIM-S-GRAY","option":[{"code":"size","value":"S"},{"code":"color","value":"gray"}]}],"price":{"amount":39.0,"currency":"USD"}}
    with open(outp,"w",encoding="utf-8") as g: g.write(json.dumps(rec)+"\n")
    print("[pim] exported ->", outp)
if __name__=="__main__":
    if len(sys.argv)<2: print("Usage: pim_export_stub.py <out.ndjson>"); sys.exit(1)
    main(sys.argv[1])
