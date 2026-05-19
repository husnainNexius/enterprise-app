-- Step 3: TRANSFORM — Staging model for orders

WITH source AS (
    SELECT * FROM {{ source('postgres', 'orders') }}
),

deduplicated AS (
    SELECT *,
        ROW_NUMBER() OVER (PARTITION BY id ORDER BY _loaded_at DESC) AS _row_num
    FROM source
),

cleaned AS (
    SELECT
        CAST(id AS INT64)                           AS order_id,
        CAST(user_id AS INT64)                      AS user_id,
        CAST(product_id AS INT64)                   AS product_id,
        CAST(quantity AS INT64)                      AS quantity,
        ROUND(CAST(amount_cents AS NUMERIC) / 100, 2) AS order_amount,
        UPPER(TRIM(currency))                       AS currency,
        LOWER(TRIM(status))                         AS order_status,
        CAST(ordered_at AS TIMESTAMP)               AS ordered_at,
        CAST(shipped_at AS TIMESTAMP)               AS shipped_at,
        CAST(delivered_at AS TIMESTAMP)             AS delivered_at,
        _loaded_at
    FROM deduplicated
    WHERE _row_num = 1
)

SELECT * FROM cleaned
