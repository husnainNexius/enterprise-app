-- Step 5: QUALITY — Test: no negative revenue
-- Returns rows that violate the rule. Any rows = FAIL.

SELECT order_id, order_amount, ordered_at
FROM {{ ref('fct_orders') }}
WHERE order_amount < 0
