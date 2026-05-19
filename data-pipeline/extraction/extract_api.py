"""
Step 1: COLLECT — Extract data from REST APIs
Handles pagination, rate limiting, and retries.
"""

import requests
import json
import time
import os
from datetime import datetime
from pathlib import Path
from dotenv import load_dotenv

load_dotenv()


class APIExtractor:
    def __init__(self, base_url: str, api_key: str = None):
        self.base_url = base_url
        self.session = requests.Session()
        if api_key:
            self.session.headers.update({"Authorization": f"Bearer {api_key}"})

    def extract(self, endpoint: str, page_size: int = 100) -> list:
        """Extract all records from a paginated API endpoint."""
        all_records = []
        page = 1

        while True:
            response = self._request_with_retry(
                f"{self.base_url}/{endpoint}",
                params={"page": page, "per_page": page_size}
            )
            if response is None:
                break

            data = response.json()
            records = data if isinstance(data, list) else data.get("results", data.get("data", []))

            if not records:
                break

            all_records.extend(records)
            print(f"  Page {page}: {len(records)} records (total: {len(all_records)})")

            if len(records) < page_size:
                break
            page += 1
            time.sleep(0.5)

        return all_records

    def _request_with_retry(self, url: str, params: dict, max_retries: int = 3):
        for attempt in range(max_retries):
            try:
                resp = self.session.get(url, params=params, timeout=30)
                if resp.status_code == 200:
                    return resp
                elif resp.status_code == 429:
                    wait = int(resp.headers.get("Retry-After", 60))
                    print(f"  Rate limited. Waiting {wait}s...")
                    time.sleep(wait)
                elif resp.status_code >= 500:
                    time.sleep(2 ** attempt)
                else:
                    print(f"  Error {resp.status_code}: {resp.text[:200]}")
                    return None
            except requests.exceptions.RequestException as e:
                print(f"  Request failed: {e}. Retry {attempt + 1}/{max_retries}")
                time.sleep(2 ** attempt)
        return None


def save_raw(data: list, source_name: str, output_dir: str = "data/raw") -> str:
    """Save extracted data as timestamped JSON."""
    Path(output_dir).mkdir(parents=True, exist_ok=True)
    timestamp = datetime.utcnow().strftime("%Y%m%dT%H%M%S")
    filepath = os.path.join(output_dir, f"{source_name}_{timestamp}.json")

    output = {
        "_metadata": {
            "source": source_name,
            "extracted_at": datetime.utcnow().isoformat(),
            "record_count": len(data),
        },
        "data": data,
    }
    with open(filepath, "w") as f:
        json.dump(output, f, indent=2, default=str)

    print(f"  Saved {len(data)} records → {filepath}")
    return filepath


if __name__ == "__main__":
    base_url = os.getenv("API_BASE_URL", "https://jsonplaceholder.typicode.com")
    extractor = APIExtractor(base_url=base_url)

    for endpoint in ["users", "posts", "comments"]:
        print(f"\nExtracting: {endpoint}")
        data = extractor.extract(endpoint)
        save_raw(data, source_name=f"api_{endpoint}")

    print("\n✅ API extraction complete!")
