"""
Step 1: COLLECT — Extract data from CSV/JSON files
Watches an incoming directory and processes dropped files.
"""

import pandas as pd
import shutil
import os
from pathlib import Path
from datetime import datetime
from extraction.extract_api import save_raw


def process_incoming_files(input_dir: str = "data/incoming",
                           archive_dir: str = "data/archive"):
    """Process CSV/JSON files from incoming directory."""
    for d in [input_dir, archive_dir]:
        Path(d).mkdir(parents=True, exist_ok=True)

    files = list(Path(input_dir).glob("*.csv")) + list(Path(input_dir).glob("*.json"))

    if not files:
        print("No incoming files to process")
        return

    for file_path in files:
        print(f"Processing: {file_path.name}")

        if file_path.suffix == ".csv":
            df = pd.read_csv(file_path)
        else:
            df = pd.read_json(file_path)

        records = df.to_dict(orient="records")
        save_raw(records, source_name=f"file_{file_path.stem}")

        # Archive original
        shutil.move(str(file_path), os.path.join(archive_dir, file_path.name))
        print(f"  Archived → {archive_dir}/{file_path.name}")

    print(f"\n✅ Processed {len(files)} files")


if __name__ == "__main__":
    process_incoming_files()
