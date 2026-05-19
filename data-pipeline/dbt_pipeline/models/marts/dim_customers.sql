-- Step 3: TRANSFORM — Mart: Customer dimension table
-- Grain: one row per customer with lifetime metrics

WITH users AS (
    SELECT * FROM {{ ref('stg_postgres__users') }}
),

order_metrics AS (
    SELECT
        user_id,
        COUNT(DISTINCT order_id)            AS total_orders,
        SUM(order_amount)                   AS lifetime_spend,
        AVG(order_amount)                   AS avg_order_value,
        MIN(ordered_at)                     AS first_order_at,
        MAX(ordered_at)                     AS last_order_at,
        COUNTIF(order_status = 'delivered') AS delivered_orders,
        COUNTIF(order_status = 'cancelled') AS cancelled_orders
    FROM {{ ref('int_orders_enriched') }}
    GROUP BY user_id
),

final AS (
    SELECT
        {{ dbt_utils.generate_surrogate_key(['u.user_id']) }} AS customer_key,
        u.user_id,
        u.email,
        u.first_name,
        u.last_name,
        u.full_name,
        u.status AS account_status,
        u.created_at AS account_created_at,

        COALESCE(m.total_orders, 0)     AS total_orders,
        COALESCE(m.lifetime_spend, 0)   AS lifetime_spend,
        COALESCE(m.avg_order_value, 0)  AS avg_order_value,
        m.first_order_at,
        m.last_order_at,

        CASE
            WHEN m.lifetime_spend >= 1000 THEN 'premium'
            WHEN m.lifetime_spend >= 100  THEN 'regular'
            WHEN m.total_orders >= 1      THEN 'new'
            ELSE 'prospect'
        END AS customer_segment,

        CASE
            WHEN m.last_order_at < TIMESTAMP_SUB(CURRENT_TIMESTAMP(), INTERVAL 90 DAY) THEN 'high'
            WHEN m.last_order_at < TIMESTAMP_SUB(CURRENT_TIMESTAMP(), INTERVAL 30 DAY) THEN 'medium'
            ELSE 'low'
        END AS churn_risk,

        CURRENT_TIMESTAMP() AS _dbt_updated_at

    FROM users u
    LEFT JOIN order_metrics m ON u.user_id = m.user_id
)

SELECT * FROM final
