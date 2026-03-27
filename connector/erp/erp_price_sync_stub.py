#!/usr/bin/env python3
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
import json, sys
def main(inp, outp):
    # read price list and emit ERP sync summary
    with open(inp,"r",encoding="utf-8") as f: pl=json.load(f)
    s={"syncedPriceList": pl.get("id","unknown"), "rules": len(pl.get("rules",[]))}
    with open(outp,"w",encoding="utf-8") as g: json.dump(s,g,indent=2)
    print("[erp] synced rules:", s["rules"])
if __name__=="__main__":
    if len(sys.argv)<3: print("Usage: erp_price_sync_stub.py <price_list.json> <out.json>"); sys.exit(1)
    main(sys.argv[1], sys.argv[2])
