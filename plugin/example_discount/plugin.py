# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
def on_price_compute(ctx):
    # expects ctx={"price": float, "account": str, "segment": str}
    price = ctx.get("price", 0.0)
    seg = ctx.get("segment", "")
    if seg == "vip":
        price = round(price * 0.9, 2)  # 10% off
    return dict(ctx, price=price)

def on_product_read(ctx):
    # annotate product read with plugin marker
    meta = ctx.get("meta", {})
    meta["plugin:example_discount"] = "applied"
    ctx["meta"] = meta
    return ctx
