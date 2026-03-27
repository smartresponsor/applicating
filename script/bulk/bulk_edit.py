#!/usr/bin/env python3
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
import json, sys

def main(inp, outp, multiplier):
    out_lines = []
    with open(inp, 'r', encoding='utf-8') as f:
        for line in f:
            if not line.strip(): continue
            p = json.loads(line)
            if "price" in p and "amount" in p["price"]:
                p["price"]["amount"] = round(p["price"]["amount"] * multiplier, 2)
            for v in p.get("variant", []):
                if "price" in v and "amount" in v["price"]:
                    v["price"]["amount"] = round(v["price"]["amount"] * multiplier, 2)
            out_lines.append(json.dumps(p))
    with open(outp, "w", encoding="utf-8") as g:
        g.write("\n".join(out_lines) + "\n")
    print("[bulk] multiplier={0} -> {1}".format(multiplier, outp))

if __name__ == "__main__":
    if len(sys.argv) < 4:
        print("Usage: bulk_edit.py <input.ndjson> <output.ndjson> <multiplier>"); sys.exit(1)
    main(sys.argv[1], sys.argv[2], float(sys.argv[3]))
