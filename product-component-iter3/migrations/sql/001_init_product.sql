-- migrations/sql/001_init_product.sql
CREATE TABLE IF NOT EXISTS product (
  id UUID PRIMARY KEY,
  product_sku_value VARCHAR(64) NOT NULL,
  product_price_amount INT NOT NULL,
  product_price_currency CHAR(3) NOT NULL,
  product_stock INT NOT NULL DEFAULT 0,
  product_status VARCHAR(32) NOT NULL,
  created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL,
  updated_at TIMESTAMP WITHOUT TIME ZONE NOT NULL
);
CREATE UNIQUE INDEX IF NOT EXISTS idx_product_sku ON product(product_sku_value);

CREATE TABLE IF NOT EXISTS product_i18n (
  product_id UUID NOT NULL REFERENCES product(id) ON DELETE CASCADE,
  locale VARCHAR(5) NOT NULL,
  product_title VARCHAR(255) NOT NULL,
  product_slug VARCHAR(255) NOT NULL,
  product_description TEXT NULL,
  PRIMARY KEY (product_id, locale)
);

CREATE TABLE IF NOT EXISTS product_read (
  id UUID PRIMARY KEY,
  product_sku VARCHAR(64) NOT NULL UNIQUE,
  product_title VARCHAR(255) NOT NULL,
  product_price_amount INT NOT NULL,
  product_price_currency CHAR(3) NOT NULL,
  product_status VARCHAR(32) NOT NULL,
  product_stock INT NOT NULL,
  updated_at TIMESTAMP WITHOUT TIME ZONE NOT NULL
);

CREATE TABLE IF NOT EXISTS product_outbox (
  id UUID PRIMARY KEY,
  event_name VARCHAR(128) NOT NULL,
  payload TEXT NOT NULL,
  idempotency_key VARCHAR(128) NOT NULL UNIQUE,
  created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL,
  processed_at TIMESTAMP WITHOUT TIME ZONE NULL
);
