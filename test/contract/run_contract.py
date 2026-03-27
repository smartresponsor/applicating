#!/usr/bin/env python3
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
import json, sys, os, glob
def main():
    base = os.path.dirname(__file__)
    ok = 0; fail = 0
    for fp in glob.glob(os.path.join(base, "case-*.json")):
        with open(fp, "r", encoding="utf-8") as f:
            case = json.load(f)
        # Minimal structural checks
        if case.get("request","").startswith("/product") or case.get("request","").startswith("/variant"):
            ok += 1
        else:
            print("[contract] FAIL:", os.path.basename(fp), "bad request path")
            fail += 1
    print(f"[contract] OK={ok} FAIL={fail}")
    return 0 if fail == 0 else 1
if __name__ == "__main__":
    sys.exit(main())
