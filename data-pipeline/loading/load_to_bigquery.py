"""
Step 2 (Alt): STORE RAW — Load raw data directly into BigQuery
Alternative to GCS when you want BigQuery as the landing zone.
"""

import os
import json
import pandas as pd
from datetime import datetime
from dotenv import load_dotenv

load_dotenv()


def load_to_bigquery(json_file: str, dataset: str, table: str):
    """Load a JSON file into a BigQuery table."""
    from google.cloud import bigquery

    project = os.getenv("BIGQUERY_PROJECT")
    if not project:
        print("⚠️  BIGQUERY_PROJECT not set in .env — skipping BigQuery load")
        return

    client = bigquery.Client(project=project)
    table_ref = f"{project}.{dataset}.{table}"

    with open(json_file) as f:
        data = json.load(f)

    records = data.get("data", data)
    df = pd.DataFrame(records)
    df["_loaded_at"] = pd.Timestamp.utcnow()
    df["_source_file"] = os.path.basename(json_file)

    job_config = bigquery.LoadJobConfig(
        write_disposition=bigquery.WriteDisposition.WRITE_APPEND,
        schema_update_options=[bigquery.SchemaUpdateOption.ALLOW_FIELD_ADDITION],
        autodetect=True,
    )

    job = client.load_table_from_dataframe(df, table_ref, job_config=job_config)
    job.result()
    print(f"  Loaded {len(df)} rows → {table_ref}")


if __name__ == "__main__":
    dataset = os.getenv("BIGQUERY_DATASET_RAW", "raw_postgres")

    from pathlib import Path
    files = list(Path("data/raw").glob("*.json"))

    for f in files:
        parts = f.stem.rsplit("_", 1)[0].split("_", 1)
        table_name = parts[1] if len(parts) >= 2 else parts[0]
        print(f"Loading: {f.name} → {dataset}.{table_name}")
        load_to_bigquery(str(f), dataset, table_name)

    print("\n✅ BigQuery loading complete!")
