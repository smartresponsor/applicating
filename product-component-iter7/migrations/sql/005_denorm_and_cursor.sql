-- migrations/sql/005_denorm_and_cursor.sql
-- Индекс по updated_at + id для стабильной курсорной пагинации
CREATE INDEX IF NOT EXISTS idx_product_read_updated_id ON product_read(updated_at DESC, id ASC);
