#!/usr/bin/env python3
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
import json, sys, itertools, time
def main(template_path, out_path, target=2048):
    # Larger matrices to exceed 2048 combinations; we'll slice to target.
    size = ["XS","S","M","L","XL","XXL","3XL","4XL","5XL","TallM","TallL"]
    color = ["black","white","gray","navy","red","green","blue","yellow","purple","orange","brown","olive","beige","teal","maroon"]
    fit = ["regular","slim","oversize","tall"]
    fabric = ["cotton","wool","linen","polyester","silk"]
    t0 = time.time()
    with open(template_path, 'r', encoding='utf-8') as f: tpl = json.load(f)
    slug = tpl["slug"]
    variants = []
    for i, (a,b,c,d) in enumerate(itertools.product(size, color, fit, fabric)):
        if i >= target: break
        sku = "{}-{}-{}-{}-{}".format(slug, a, b, c, d)
        variants.append({"sku": sku, "option":[{"code":"size","value":a},{"code":"color","value":b},{"code":"fit","value":c},{"code":"fabric","value":d}]})
    tpl["variant"] = variants
    with open(out_path, "w", encoding="utf-8") as g:
        g.write(json.dumps(tpl) + "\n")
    print("[gen_v2] variants={} -> {}".format(len(variants), out_path))
if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Usage: generate_variants_v2.py <product-template.json> <output.ndjson> [target]"); sys.exit(1)
    target = int(sys.argv[3]) if len(sys.argv) > 3 else 2048
    main(sys.argv[1], sys.argv[2], target)
