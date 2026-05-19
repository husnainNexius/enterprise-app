-- Step 5: QUALITY — Test: every order has a matching customer

SELECT o.order_id, o.user_id
FROM {{ ref('fct_orders') }} o
LEFT JOIN {{ ref('dim_customers') }} c ON o.customer_key = c.customer_key
WHERE c.customer_key IS NULL
