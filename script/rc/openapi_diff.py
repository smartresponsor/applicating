#!/usr/bin/env python3
import json, sys
def main(old_path, new_path, out_path):
    with open(old_path,"r",encoding="utf-8") as f: old=json.load(f)
    with open(new_path,"r",encoding="utf-8") as f: new=json.load(f)
    old_paths=set((k, tuple(sorted(v.keys()))) for k,v in old.get("paths",{}).items())
    new_paths=set((k, tuple(sorted(v.keys()))) for k,v in new.get("paths",{}).items())
    added=[p for p in new_paths-old_paths]
    removed=[p for p in old_paths-new_paths]
    report={"added": [a[0] for a in added], "removed": [r[0] for r in removed]}
    with open(out_path,"w",encoding="utf-8") as g: json.dump(report,g,indent=2)
    print("[openapi-diff]", report)
if __name__=="__main__":
    if len(sys.argv)<4:
        print("Usage: openapi_diff.py <old.json> <new.json> <out.json>"); sys.exit(1)
    main(sys.argv[1], sys.argv[2], sys.argv[3])
