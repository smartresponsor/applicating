#!/usr/bin/env python3
import json, sys
def on_price_compute(ctx):
    # VIP 10% off
    if ctx.get("segment")=="vip":
        ctx["price"] = round(ctx.get("price",0.0)*0.9,2)
    return ctx
if __name__=="__main__":
    if len(sys.argv)<2: print("Usage: plugin_loader.py <ctx.json>"); sys.exit(1)
    ctx=json.loads(sys.argv[1])
    print(json.dumps(on_price_compute(ctx)))
