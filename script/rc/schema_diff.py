#!/usr/bin/env python3
import os, sys, hashlib, json
def digest(fp):
    h=hashlib.sha256()
    with open(fp,"rb") as f:
        for chunk in iter(lambda:f.read(8192), b""):
            h.update(chunk)
    return h.hexdigest()
def walk(dirp):
    out={}
    for root,_,files in os.walk(dirp):
        for fn in files:
            if fn.endswith(".json"):
                p=os.path.join(root, fn)
                out[os.path.relpath(p, dirp)]=digest(p)
    return out
def main(old_dir, new_dir, out_json):
    A=walk(old_dir); B=walk(new_dir)
    added=[k for k in B.keys() if k not in A]
    removed=[k for k in A.keys() if k not in B]
    changed=[k for k in B.keys() if k in A and A[k]!=B[k]]
    rep={"added":added,"removed":removed,"changed":changed}
    with open(out_json,"w",encoding="utf-8") as g: json.dump(rep,g,indent=2)
    print("[schema-diff]", rep)
if __name__=="__main__":
    if len(sys.argv)<4:
        print("Usage: schema_diff.py <old_dir> <new_dir> <out.json>"); sys.exit(1)
    main(sys.argv[1], sys.argv[2], sys.argv[3])
