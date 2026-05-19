"""
Step 2: STORE RAW — Upload extracted data to Google Cloud Storage
Converts JSON to Parquet and organizes with Hive-style partitioning.
"""

import os
import json
import pandas as pd
import pyarrow as pa
import pyarrow.parquet as pq
from datetime import datetime
from pathlib import Path
from dotenv import load_dotenv

load_dotenv()


class GCSUploader:
    def __init__(self, bucket_name: str):
        from google.cloud import storage
        self.client = storage.Client()
        self.bucket = self.client.bucket(bucket_name)

    def upload_json_as_parquet(self, local_file: str, source: str, entity: str) -> str:
        """Read JSON, convert to Parquet, upload to GCS with partitioning."""
        with open(local_file) as f:
            data = json.load(f)

        records = data.get("data", data)
        if not records:
            print(f"  No data in {local_file}, skipping")
            return None

        df = pd.DataFrame(records)
        now = datetime.utcnow()
        ts = now.strftime("%H%M%S")

        gcs_path = (
            f"raw/{source}/{entity}/"
            f"year={now.year}/month={now.month:02d}/day={now.day:02d}/"
            f"{entity}_{ts}.parquet"
        )

        # Write parquet to temp, upload, cleanup
        tmp = f"{entity}_{ts}.parquet"
        table = pa.Table.from_pandas(df)
        pq.write_table(table, tmp, compression="snappy")

        blob = self.bucket.blob(gcs_path)
        blob.upload_from_filename(tmp)
        blob.metadata = {
            "source": source, "entity": entity,
            "record_count": str(len(df)),
            "uploaded_at": now.isoformat(),
        }
        blob.patch()
        os.remove(tmp)

        uri = f"gs://{self.bucket.name}/{gcs_path}"
        print(f"  Uploaded {len(df)} rows → {uri}")
        return uri


def upload_all(raw_dir: str = "data/raw"):
    """Upload all JSON files from raw directory to GCS."""
    bucket = os.getenv("GCS_BUCKET_RAW")
    if not bucket:
        print("⚠️  GCS_BUCKET_RAW not set in .env — skipping GCS upload")
        print("   Copy .env.example to .env and configure GCS settings")
        return

    uploader = GCSUploader(bucket)
    files = list(Path(raw_dir).glob("*.json"))
    print(f"Found {len(files)} files to upload\n")

    for fp in files:
        parts = fp.stem.rsplit("_", 1)
        name_parts = parts[0].split("_", 1)
        source = name_parts[0] if len(name_parts) >= 2 else "unknown"
        entity = name_parts[1] if len(name_parts) >= 2 else name_parts[0]
        uploader.upload_json_as_parquet(str(fp), source, entity)

    print(f"\n✅ All files uploaded to gs://{bucket}/raw/")


if __name__ == "__main__":
    upload_all()
