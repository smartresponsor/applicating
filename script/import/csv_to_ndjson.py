#!/usr/bin/env python3
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
import csv, json, sys
def main(inp, outp):
    with open(inp, newline='', encoding='utf-8') as f, open(outp, 'w', encoding='utf-8') as g:
        r = csv.DictReader(f)
        products = {}
        for row in r:
            pid = row['id']
            if pid not in products:
                products[pid] = {"id": pid, "slug": row['slug'], "title": row['title'], "variant": [],
                                  "price": {"amount": float(row['price_amount']), "currency": row['price_currency']}}
            products[pid]["variant"].append({"sku": row['sku'], "option":[
                {"code":"size","value": row['option_size']},
                {"code":"color","value": row['option_color']}
            ]})
        for p in products.values():
            g.write(json.dumps(p) + "\n")
if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Usage: csv_to_ndjson.py <input.csv> <output.ndjson>"); sys.exit(1)
    main(sys.argv[1], sys.argv[2])
