#!/usr/bin/env python3
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
import json, sys, time
cache={}
def get(key, ttl):
    v=cache.get(key)
    if v and (time.time()-v["ts"])<ttl:
        return v["val"], True
    return None, False
def setv(key, val):
    cache[key]={"val":val,"ts":time.time()}
def main():
    setv("product:pro-tee", {"slug":"pro-tee","title":"Pro Tee"})
    v, hit = get("product:pro-tee", ttl=60)
    print("[cache] hit=", hit, "value=", v)
if __name__=="__main__":
    main()
