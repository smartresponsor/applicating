#!/usr/bin/env python3
import json, sys
def main(price_list_json, out_json):
    with open(price_list_json,"r",encoding="utf-8") as f: pl=json.load(f)
    res={"syncedPriceList": pl.get("id","unknown"), "rules": len(pl.get("rules",[]))}
    with open(out_json,"w",encoding="utf-8") as g: json.dump(res,g,indent=2)
    print("[erp] sync ->", out_json)
if __name__=="__main__":
    if len(sys.argv)<3: print("Usage: erp_sync.py <price_list.json> <out.json>"); sys.exit(1)
    main(sys.argv[1], sys.argv[2])
