-- Macro: Convert cents to dollars safely
{% macro cents_to_dollars(column_name) %}
    ROUND(CAST({{ column_name }} AS NUMERIC) / 100, 2)
{% endmacro %}
