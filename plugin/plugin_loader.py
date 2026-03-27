#!/usr/bin/env python3
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
import importlib.util, json, os, sys

def load_plugin(manifest_path):
    with open(manifest_path, "r", encoding="utf-8") as f:
        m = json.load(f)
    entry = os.path.join(os.path.dirname(manifest_path), m["entry"])
    spec = importlib.util.spec_from_file_location(m["name"], entry)
    mod = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)
    return m, mod

def run_hooks(mod, hook, ctx):
    fn = getattr(mod, hook, None)
    if not fn:
        return ctx
    return fn(ctx)

def main(manifest_dir, hook, input_json):
    ctx = json.loads(input_json)
    for root, _, files in os.walk(manifest_dir):
        for fn in files:
            if fn == "plugin.json":
                m, mod = load_plugin(os.path.join(root, fn))
                if hook in m.get("hooks", []):
                    ctx = run_hooks(mod, hook, ctx)
    print(json.dumps(ctx))

if __name__ == "__main__":
    if len(sys.argv) < 4:
        print("Usage: plugin_loader.py <plugins_dir> <hook_name> <input_json>")
        sys.exit(1)
    main(sys.argv[1], sys.argv[2], sys.argv[3])
