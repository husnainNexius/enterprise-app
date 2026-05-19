-- Step 3: TRANSFORM — Intermediate: enrich orders with product details

WITH orders AS (
    SELECT * FROM {{ ref('stg_postgres__orders') }}
),

products AS (
    SELECT * FROM {{ ref('stg_postgres__products') }}
),

enriched AS (
    SELECT
        o.order_id,
        o.user_id,
        o.product_id,
        o.quantity,
        o.order_amount,
        o.currency,
        o.order_status,
        o.ordered_at,
        o.shipped_at,
        o.delivered_at,

        p.product_name,
        p.category AS product_category,
        p.price    AS unit_price,

        o.order_amount * o.quantity AS total_line_amount,
        TIMESTAMP_DIFF(o.delivered_at, o.ordered_at, DAY) AS days_to_deliver
    FROM orders o
    LEFT JOIN products p ON o.product_id = p.product_id
)

SELECT * FROM enriched
