#!/usr/bin/env python3
import json, sys, time, uuid
def main(event_type, payload_ndjson, out_json):
    with open(payload_ndjson,"r",encoding="utf-8") as f: line=f.readline()
    evt={"id":str(uuid.uuid4()),"type":event_type,"createdAt":time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),"payload":json.loads(line)}
    with open(out_json,"w",encoding="utf-8") as g: json.dump(evt,g,indent=2)
    print("[webhook] ->", out_json)
if __name__=="__main__":
    if len(sys.argv)<4: print("Usage: publish.py <event_type> <payload.ndjson> <out.json>"); sys.exit(1)
    main(sys.argv[1], sys.argv[2], sys.argv[3])
