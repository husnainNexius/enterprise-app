-- Step 3: TRANSFORM — Staging model for users
-- Light cleaning only: rename, cast, deduplicate. NO business logic.

WITH source AS (
    SELECT * FROM {{ source('postgres', 'users') }}
),

deduplicated AS (
    SELECT *,
        ROW_NUMBER() OVER (PARTITION BY id ORDER BY _loaded_at DESC) AS _row_num
    FROM source
),

cleaned AS (
    SELECT
        CAST(id AS INT64)                               AS user_id,
        LOWER(TRIM(email))                              AS email,
        TRIM(first_name)                                AS first_name,
        TRIM(last_name)                                 AS last_name,
        CONCAT(TRIM(first_name), ' ', TRIM(last_name)) AS full_name,
        LOWER(TRIM(status))                             AS status,
        CAST(created_at AS TIMESTAMP)                   AS created_at,
        CAST(updated_at AS TIMESTAMP)                   AS updated_at,
        _loaded_at
    FROM deduplicated
    WHERE _row_num = 1
      AND email IS NOT NULL
)

SELECT * FROM cleaned
