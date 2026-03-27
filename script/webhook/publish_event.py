#!/usr/bin/env python3
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
import json, sys, time, hmac, hashlib, uuid
def sign(secret, payload):
    mac=hmac.new(secret.encode(), payload.encode(), hashlib.sha256).hexdigest()
    return mac
def main(event_type, payload_path, out_path, secret="dev-secret"):
    with open(payload_path,"r",encoding="utf-8") as f:
        lines=f.read().strip().splitlines()
    data=lines[0]
    evt={"id":str(uuid.uuid4()),"type":event_type,"createdAt":time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),"payload":json.loads(data)}
    raw=json.dumps(evt, separators=(",",":"))
    evt["signature"]=sign(secret, raw)
    with open(out_path,"w",encoding="utf-8") as g:
        json.dump(evt, g, indent=2)
    print("[webhook] {} -> {}".format(event_type, out_path))
if __name__=="__main__":
    if len(sys.argv) < 4:
        print("Usage: publish_event.py <event_type> <payload.ndjson> <out.json> [secret]"); sys.exit(1)
    secret=sys.argv[4] if len(sys.argv)>4 else "dev-secret"
    main(sys.argv[1], sys.argv[2], sys.argv[3], secret)
