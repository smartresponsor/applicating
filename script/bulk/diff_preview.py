#!/usr/bin/env python3
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
import json, sys, difflib
def main(old, new, outp):
    with open(old, 'r', encoding='utf-8') as f: a = [l.rstrip() for l in f.readlines()]
    with open(new, 'r', encoding='utf-8') as f: b = [l.rstrip() for l in f.readlines()]
    diff = difflib.unified_diff(a, b, fromfile=old, tofile=new, lineterm='')
    with open(outp, 'w', encoding='utf-8') as g:
        for line in diff:
            g.write(line + "\n")
    print("[diff] -> {0}".format(outp))

if __name__ == "__main__":
    if len(sys.argv) < 4:
        print("Usage: diff_preview.py <old.ndjson> <new.ndjson> <out.diff>"); sys.exit(1)
    main(sys.argv[1], sys.argv[2], sys.argv[3])
