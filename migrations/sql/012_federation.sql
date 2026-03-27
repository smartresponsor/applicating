-- Federation views
CREATE OR REPLACE VIEW federated_products AS
SELECT tenant_id, COUNT(*) AS total_products, SUM(product_price_amount) AS total_value
FROM product GROUP BY tenant_id;

CREATE OR REPLACE VIEW federated_orders AS
SELECT tenant_id, COUNT(*) AS total_orders, SUM(order_total_amount) AS total_revenue
FROM orders GROUP BY tenant_id;

CREATE MATERIALIZED VIEW federated_metrics AS
SELECT p.tenant_id, p.total_products, p.total_value, o.total_orders, o.total_revenue
FROM federated_products p LEFT JOIN federated_orders o USING (tenant_id);
