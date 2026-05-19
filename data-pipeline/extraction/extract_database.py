"""
Step 1: COLLECT — Extract data from a database (PostgreSQL)
Supports full load and incremental load with cursor/watermark tracking.
"""

import json
import os
from datetime import datetime
from pathlib import Path
from dotenv import load_dotenv

load_dotenv()


class DatabaseExtractor:
    def __init__(self, connection_string: str):
        from sqlalchemy import create_engine
        self.engine = create_engine(connection_string)

    def full_extract(self, table: str, schema: str = "public") -> list:
        """Extract ALL rows from a table."""
        from sqlalchemy import text
        with self.engine.connect() as conn:
            result = conn.execute(text(f'SELECT * FROM "{schema}"."{table}"'))
            columns = list(result.keys())
            rows = [dict(zip(columns, row)) for row in result.fetchall()]
        print(f"  Full extract: {len(rows)} rows from {schema}.{table}")
        return rows

    def incremental_extract(self, table: str, cursor_col: str = "updated_at",
                            last_value: str = None, schema: str = "public") -> tuple:
        """Extract only new/changed rows since last cursor value."""
        from sqlalchemy import text
        if last_value:
            query = f'SELECT * FROM "{schema}"."{table}" WHERE "{cursor_col}" > :cursor ORDER BY "{cursor_col}"'
            params = {"cursor": last_value}
        else:
            query = f'SELECT * FROM "{schema}"."{table}" ORDER BY "{cursor_col}"'
            params = {}

        with self.engine.connect() as conn:
            result = conn.execute(text(query), params)
            columns = list(result.keys())
            rows = [dict(zip(columns, row)) for row in result.fetchall()]

        new_cursor = str(rows[-1][cursor_col]) if rows else last_value
        print(f"  Incremental: {len(rows)} new rows from {table}")
        return rows, new_cursor


# --- State Management (tracks where we left off) ---

def load_state(source: str, state_dir: str = "state") -> dict:
    path = os.path.join(state_dir, f"{source}_state.json")
    if os.path.exists(path):
        with open(path) as f:
            return json.load(f)
    return {}


def save_state(source: str, state: dict, state_dir: str = "state"):
    Path(state_dir).mkdir(parents=True, exist_ok=True)
    with open(os.path.join(state_dir, f"{source}_state.json"), "w") as f:
        json.dump(state, f, indent=2)


if __name__ == "__main__":
    conn_str = os.getenv("SOURCE_DB_URL")

    if not conn_str:
        print("⚠️  SOURCE_DB_URL not set in .env — skipping database extraction")
        print("   Copy .env.example to .env and add your database connection string")
        exit(0)

    from extraction.extract_api import save_raw

    extractor = DatabaseExtractor(conn_str)
    state = load_state("postgres")

    for table in ["users", "orders", "products"]:
        last = state.get(table, {}).get("cursor")
        rows, new_cursor = extractor.incremental_extract(table, cursor_col="updated_at", last_value=last)

        if rows:
            save_raw(rows, source_name=f"postgres_{table}")
            state[table] = {"cursor": new_cursor, "extracted_at": datetime.utcnow().isoformat()}

    save_state("postgres", state)
    print("\n✅ Database extraction complete!")
