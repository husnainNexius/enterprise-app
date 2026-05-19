-- Step 3: TRANSFORM — Mart: Orders fact table
-- Grain: one row per order

{{
    config(
        materialized='incremental',
        unique_key='order_id',
        partition_by={"field": "ordered_at", "data_type": "timestamp", "granularity": "month"},
        cluster_by=['order_status', 'user_id']
    )
}}

WITH orders AS (
    SELECT * FROM {{ ref('int_orders_enriched') }}

    {% if is_incremental() %}
    WHERE ordered_at > (SELECT MAX(ordered_at) FROM {{ this }})
    {% endif %}
),

final AS (
    SELECT
        order_id,
        {{ dbt_utils.generate_surrogate_key(['user_id']) }}    AS customer_key,
        {{ dbt_utils.generate_surrogate_key(['product_id']) }} AS product_key,
        user_id,
        product_id,
        quantity,
        order_amount,
        total_line_amount,
        currency,
        order_status,
        product_name,
        product_category,
        ordered_at,
        shipped_at,
        delivered_at,
        days_to_deliver,

        DATE(ordered_at)                    AS order_date,
        EXTRACT(YEAR FROM ordered_at)       AS order_year,
        EXTRACT(MONTH FROM ordered_at)      AS order_month,

        CURRENT_TIMESTAMP() AS _dbt_updated_at

    FROM orders
)

SELECT * FROM final
