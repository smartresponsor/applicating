# ER Model (lite)
Entities:
- product: id, slug, title, attribute[], variant[], price, media[], i18n
- variant: sku, title, option[], price, media[], active
- attribute: code, type, value
- option: code, value
- price: amount, currency, compareAt?
- media: url, alt?
- i18n: { locale: { title, description } }
Invariants:
- variant.option combination must be unique per product
- slug must match ^[a-z0-9-]+$
