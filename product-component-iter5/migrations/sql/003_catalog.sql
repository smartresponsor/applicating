-- migrations/sql/003_catalog.sql
CREATE TABLE IF NOT EXISTS product_variant (
  id UUID PRIMARY KEY,
  product_id UUID NOT NULL REFERENCES product(id) ON DELETE CASCADE,
  variant_sku VARCHAR(64) NOT NULL UNIQUE,
  variant_price_amount INT NOT NULL,
  variant_price_currency CHAR(3) NOT NULL,
  variant_stock INT NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS product_option (
  id UUID PRIMARY KEY,
  product_id UUID NOT NULL REFERENCES product(id) ON DELETE CASCADE,
  option_code VARCHAR(64) NOT NULL
);

CREATE TABLE IF NOT EXISTS product_option_value (
  id UUID PRIMARY KEY,
  option_id UUID NOT NULL REFERENCES product_option(id) ON DELETE CASCADE,
  value VARCHAR(128) NOT NULL
);

CREATE TABLE IF NOT EXISTS product_attribute (
  id UUID PRIMARY KEY,
  product_id UUID NOT NULL REFERENCES product(id) ON DELETE CASCADE,
  attr_code VARCHAR(64) NOT NULL,
  attr_value VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS product_category (
  id UUID PRIMARY KEY,
  category_slug VARCHAR(128) NOT NULL UNIQUE,
  category_title VARCHAR(255) NOT NULL,
  parent_id UUID NULL REFERENCES product_category(id) ON DELETE SET NULL
);
