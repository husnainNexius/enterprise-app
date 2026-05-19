-- Step 3: TRANSFORM — Staging model for products

WITH source AS (
    SELECT * FROM {{ source('postgres', 'products') }}
),

deduplicated AS (
    SELECT *,
        ROW_NUMBER() OVER (PARTITION BY id ORDER BY _loaded_at DESC) AS _row_num
    FROM source
),

cleaned AS (
    SELECT
        CAST(id AS INT64)                   AS product_id,
        TRIM(name)                          AS product_name,
        TRIM(category)                      AS category,
        ROUND(CAST(price_cents AS NUMERIC) / 100, 2) AS price,
        LOWER(TRIM(status))                 AS product_status,
        CAST(created_at AS TIMESTAMP)       AS created_at,
        _loaded_at
    FROM deduplicated
    WHERE _row_num = 1
)

SELECT * FROM cleaned
