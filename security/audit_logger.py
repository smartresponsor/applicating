#!/usr/bin/env python3
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
import json, sys, time
def log(actor, action, entity, data, outp):
    evt={"ts": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()), "actor":actor,"action":action,"entity":entity,"data":data}
    with open(outp,"a",encoding="utf-8") as g: g.write(json.dumps(evt)+"\n")
    print("[audit]", action, "->", outp)
if __name__=="__main__":
    if len(sys.argv)<6: print("Usage: audit_logger.py <actor> <action> <entity> <data.json> <out.log>"); sys.exit(1)
    actor, action, entity, data_json, outp = sys.argv[1:6]
    with open(data_json,"r",encoding="utf-8") as f: data=json.load(f)
    log(actor, action, entity, data, outp)
