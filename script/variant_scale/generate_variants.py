#!/usr/bin/env python3
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
import json, sys, itertools, time

def main(template_path, cfg_path, out_path, min_count=650):
    t0 = time.time()
    with open(template_path, 'r', encoding='utf-8') as f: tpl = json.load(f)
    with open(cfg_path, 'r', encoding='utf-8') as f: cfg = json.load(f)
    m = cfg['optionMatrix']
    slug = tpl['slug']
    sku_t = cfg.get('skuTemplate', '{slug}-{size}-{color}-{fit}-{fabric}')
    combos = itertools.product(m['size'], m['color'], m['fit'], m['fabric'])
    variant = []
    for size, color, fit, fabric in combos:
        sku = sku_t.format(slug=slug, size=size, color=color, fit=fit, fabric=fabric)
        variant.append({
            "sku": sku,
            "option": [
                {"code":"size","value":size},
                {"code":"color","value":color},
                {"code":"fit","value":fit},
                {"code":"fabric","value":fabric}
            ]
        })
        if len(variant) >= min_count:
            break
    tpl['variant'] = variant
    with open(out_path, 'w', encoding='utf-8') as g:
        g.write(json.dumps(tpl) + "\n")
    dur = (time.time() - t0) * 1000
    print("[gen] variants={0} time_ms={1} -> {2}".format(len(variant), int(dur), out_path))

if __name__ == "__main__":
    if len(sys.argv) < 4:
        print("Usage: generate_variants.py <product-template.json> <config.json> <output.ndjson> [min_count]")
        sys.exit(1)
    min_count = int(sys.argv[4]) if len(sys.argv) > 4 else 650
    main(sys.argv[1], sys.argv[2], sys.argv[3], min_count)
