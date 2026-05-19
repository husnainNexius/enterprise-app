"""
Step 7: SERVE — FastAPI data endpoints
Serves warehouse data to applications and dashboards.

Run: uvicorn api.main:app --reload --port 8000
Docs: http://localhost:8000/docs
"""

import os
from datetime import datetime, date
from typing import Optional
from fastapi import FastAPI, Query, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from dotenv import load_dotenv

load_dotenv()

app = FastAPI(
    title="Data Pipeline API",
    description="REST API serving analytics data from BigQuery",
    version="1.0.0",
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["GET"],
    allow_headers=["*"],
)


def get_bq_client():
    """Get BigQuery client (lazy init)."""
    from google.cloud import bigquery
    project = os.getenv("BIGQUERY_PROJECT")
    if not project:
        return None
    return bigquery.Client(project=project)


def query_bq(sql: str, params: list = None) -> list:
    """Execute BigQuery query, return list of dicts."""
    from google.cloud import bigquery
    client = get_bq_client()
    if not client:
        raise HTTPException(500, "BigQuery not configured. Set BIGQUERY_PROJECT in .env")
    config = bigquery.QueryJobConfig(query_parameters=params) if params else None
    return [dict(row) for row in client.query(sql, job_config=config)]


# ==================== ENDPOINTS ====================

@app.get("/")
def health():
    return {"status": "healthy", "timestamp": datetime.utcnow().isoformat()}


@app.get("/api/v1/revenue/daily")
def daily_revenue(
    start_date: date = Query(..., description="Start (YYYY-MM-DD)"),
    end_date: date = Query(..., description="End (YYYY-MM-DD)"),
):
    """Daily revenue metrics for a date range."""
    from google.cloud import bigquery
    sql = """
        SELECT order_date, COUNT(*) AS orders,
               SUM(order_amount) AS revenue,
               AVG(order_amount) AS avg_order
        FROM `analytics.fct_orders`
        WHERE order_date BETWEEN @s AND @e
        GROUP BY order_date ORDER BY order_date DESC
    """
    params = [
        bigquery.ScalarQueryParameter("s", "DATE", str(start_date)),
        bigquery.ScalarQueryParameter("e", "DATE", str(end_date)),
    ]
    return {"data": query_bq(sql, params)}


@app.get("/api/v1/customers")
def list_customers(
    segment: Optional[str] = None,
    limit: int = Query(50, ge=1, le=500),
    offset: int = Query(0, ge=0),
):
    """List customers with optional segment filter."""
    from google.cloud import bigquery
    where = "WHERE customer_segment = @seg" if segment else ""
    sql = f"""
        SELECT customer_key, user_id, full_name, customer_segment,
               churn_risk, total_orders, lifetime_spend
        FROM `analytics.dim_customers`
        {where}
        ORDER BY lifetime_spend DESC
        LIMIT @lim OFFSET @off
    """
    params = [
        bigquery.ScalarQueryParameter("lim", "INT64", limit),
        bigquery.ScalarQueryParameter("off", "INT64", offset),
    ]
    if segment:
        params.append(bigquery.ScalarQueryParameter("seg", "STRING", segment))
    return {"data": query_bq(sql, params)}


@app.get("/api/v1/customers/{user_id}")
def get_customer(user_id: int):
    """Get a single customer profile."""
    from google.cloud import bigquery
    sql = "SELECT * FROM `analytics.dim_customers` WHERE user_id = @uid"
    params = [bigquery.ScalarQueryParameter("uid", "INT64", user_id)]
    data = query_bq(sql, params)
    if not data:
        raise HTTPException(404, "Customer not found")
    return {"data": data[0]}


@app.get("/api/v1/segments/summary")
def segment_summary():
    """Customer segment breakdown."""
    sql = """
        SELECT customer_segment, COUNT(*) AS count,
               ROUND(AVG(lifetime_spend),2) AS avg_spend,
               SUM(lifetime_spend) AS total_spend
        FROM `analytics.dim_customers`
        GROUP BY customer_segment
        ORDER BY total_spend DESC
    """
    return {"data": query_bq(sql)}
