#!/usr/bin/env python3
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
import json, sys, os, shutil
def main(src_ndjson, out_dir):
    os.makedirs(out_dir, exist_ok=True)
    # naive 'replication' - copy first line to a regional file
    with open(src_ndjson,"r",encoding="utf-8") as f:
        first=f.readline()
    with open(os.path.join(out_dir,"replica.ndjson"),"w",encoding="utf-8") as g:
        g.write(first)
    print("[xregion] replicated ->", out_dir)
if __name__=="__main__":
    if len(sys.argv)<3: print("Usage: xregion_replica.py <in.ndjson> <out_dir>"); sys.exit(1)
    main(sys.argv[1], sys.argv[2])
