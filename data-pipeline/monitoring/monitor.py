"""
Step 8: MONITOR — Pipeline health monitoring with alerting
Checks freshness, row counts, nulls, and sends Slack/email alerts.
"""

import os
import json
import requests
from datetime import datetime
from dataclasses import dataclass, field
from dotenv import load_dotenv

load_dotenv()


@dataclass
class CheckResult:
    name: str
    status: str  # PASS, WARN, FAIL
    message: str
    value: float = 0


class PipelineMonitor:
    def __init__(self, project_id: str):
        from google.cloud import bigquery
        self.client = bigquery.Client(project=project_id)
        self.results: list = []

    def check_freshness(self, dataset: str, table: str, max_hours: int = 24):
        """Alert if table hasn't been updated within max_hours."""
        query = f"""
            SELECT TIMESTAMP_MILLIS(last_modified_time) AS last_mod
            FROM `{dataset}.__TABLES__` WHERE table_id = '{table}'
        """
        rows = list(self.client.query(query).result())
        if not rows:
            self.results.append(CheckResult(f"freshness_{table}", "FAIL", f"Table {table} not found"))
            return

        hours = (datetime.utcnow() - rows[0].last_mod.replace(tzinfo=None)).total_seconds() / 3600
        if hours > max_hours:
            self.results.append(CheckResult(f"freshness_{table}", "FAIL",
                                            f"STALE: {table} is {hours:.1f}h old (max: {max_hours}h)", hours))
        else:
            self.results.append(CheckResult(f"freshness_{table}", "PASS",
                                            f"FRESH: {table} is {hours:.1f}h old", hours))

    def check_row_count(self, dataset: str, table: str, min_rows: int):
        """Alert if table has fewer rows than expected."""
        rows = list(self.client.query(f"SELECT COUNT(*) c FROM `{dataset}.{table}`").result())
        count = rows[0].c
        if count < min_rows:
            self.results.append(CheckResult(f"rows_{table}", "FAIL",
                                            f"LOW: {table} has {count:,} rows (min: {min_rows:,})", count))
        else:
            self.results.append(CheckResult(f"rows_{table}", "PASS",
                                            f"OK: {table} has {count:,} rows", count))

    def check_null_rate(self, dataset: str, table: str, column: str, max_pct: float = 0):
        """Alert if null percentage exceeds threshold."""
        rows = list(self.client.query(
            f"SELECT ROUND(COUNTIF({column} IS NULL)/COUNT(*)*100,2) p FROM `{dataset}.{table}`"
        ).result())
        pct = rows[0].p
        status = "FAIL" if pct > max_pct else "PASS"
        self.results.append(CheckResult(f"nulls_{table}.{column}", status,
                                        f"{table}.{column} null rate: {pct}%", pct))

    def send_slack(self, webhook_url: str = None):
        """Send results to Slack."""
        url = webhook_url or os.getenv("SLACK_WEBHOOK_URL")
        if not url:
            print("⚠️ SLACK_WEBHOOK_URL not set — skipping Slack alert")
            return

        failures = [r for r in self.results if r.status == "FAIL"]
        if failures:
            header = f"🔴 Pipeline Monitor: {len(failures)} FAILURES"
            lines = [f"• {r.message}" for r in failures]
        else:
            header = f"🟢 All {len(self.results)} checks passed"
            lines = []

        requests.post(url, json={"text": f"*{header}*\n" + "\n".join(lines)})

    def get_summary(self) -> dict:
        return {
            "timestamp": datetime.utcnow().isoformat(),
            "total": len(self.results),
            "passed": len([r for r in self.results if r.status == "PASS"]),
            "failures": len([r for r in self.results if r.status == "FAIL"]),
            "details": [{"name": r.name, "status": r.status, "message": r.message} for r in self.results],
        }


if __name__ == "__main__":
    project = os.getenv("GCP_PROJECT_ID")
    if not project:
        print("⚠️  GCP_PROJECT_ID not set in .env — skipping monitoring")
        print("   This script requires BigQuery access. Set credentials in .env")
        exit(0)

    m = PipelineMonitor(project)
    m.check_freshness("analytics", "fct_orders", max_hours=24)
    m.check_freshness("analytics", "dim_customers", max_hours=24)
    m.check_row_count("analytics", "fct_orders", min_rows=100)
    m.check_null_rate("analytics", "fct_orders", "order_id", max_pct=0)

    summary = m.get_summary()
    print(json.dumps(summary, indent=2))
    m.send_slack()

    if summary["failures"] > 0:
        print(f"\n❌ {summary['failures']} checks failed!")
        exit(1)
    print(f"\n✅ All {summary['total']} checks passed!")
