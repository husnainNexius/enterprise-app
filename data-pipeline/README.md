# Data Engineering Pipeline

A complete, minimal data engineering pipeline covering all 9 steps.

## Project Structure

```
data-pipeline/
├── extraction/                  # Step 1: COLLECT
│   ├── extract_api.py           #   API extraction with pagination
│   ├── extract_database.py      #   Database extraction with incremental loads
│   └── extract_files.py         #   CSV/JSON file processing
│
├── loading/                     # Step 2: STORE RAW
│   ├── upload_to_gcs.py         #   Upload to Google Cloud Storage (Parquet)
│   └── load_to_bigquery.py      #   Load directly to BigQuery
│
├── dbt_pipeline/                # Step 3 & 5: TRANSFORM + QUALITY
│   ├── dbt_project.yml
│   ├── packages.yml
│   ├── profiles.yml.example     #   ← Copy to ~/.dbt/profiles.yml
│   ├── models/
│   │   ├── staging/postgres/    #   Clean raw data (views)
│   │   ├── intermediate/        #   Join & enrich (CTEs)
│   │   └── marts/               #   Business-ready tables
│   ├── tests/                   #   Data quality assertions
│   └── macros/                  #   Reusable SQL functions
│
├── orchestration/               # Step 6: SCHEDULE
│   └── dags/
│       └── data_pipeline_dag.py #   Airflow DAG (daily pipeline)
│
├── api/                         # Step 7: SERVE
│   └── main.py                  #   FastAPI data endpoints
│
├── monitoring/                  # Step 8: MONITOR
│   └── monitor.py               #   Health checks + Slack alerts
│
├── governance/                  # Step 9: CATALOG & GOVERN
│   └── contracts/
│       └── users_contract.yml   #   Data contract definition
│
├── .env.example                 #   ← Copy to .env, add your credentials
├── .gitignore
├── requirements.txt
└── README.md
```

## Quick Start

### 1. Setup
```bash
cd data-pipeline

# Create virtual environment
python -m venv venv
.\venv\Scripts\activate          # Windows

# Install dependencies
pip install -r requirements.txt

# Configure credentials
copy .env.example .env           # Then edit .env with your values
```

### 2. Run Step 1 (Extract) — Works immediately!
```bash
python -m extraction.extract_api
# Extracts from JSONPlaceholder (free test API), saves to data/raw/
```

### 3. Run Step 2 (Store Raw) — Needs GCS credentials
```bash
# First add GCS_BUCKET_RAW and service account to .env
python -m loading.upload_to_gcs
```

### 4. Run Step 3 (Transform) — Needs BigQuery credentials
```bash
# First: copy dbt_pipeline/profiles.yml.example to ~/.dbt/profiles.yml
# Edit with your BigQuery project ID and service account path
cd dbt_pipeline
dbt deps          # Install packages
dbt run           # Run all models
dbt test          # Run quality checks
dbt docs serve    # View catalog at localhost:8001
```

### 5. Run Step 7 (Serve API) — Needs BigQuery credentials
```bash
uvicorn api.main:app --reload --port 8000
# Docs at http://localhost:8000/docs
```

### 6. Run Step 8 (Monitor)
```bash
python -m monitoring.monitor
```

## Where to Add Credentials

| File | What to Add |
|------|-------------|
| `.env` | All API keys, DB URLs, GCP project IDs, Slack webhook |
| `credentials/service-account.json` | GCP service account key (download from GCP Console) |
| `~/.dbt/profiles.yml` | BigQuery connection for dbt (copy from `profiles.yml.example`) |

## Pipeline Flow

```
Extract (APIs/DB/Files)
    ↓
Store Raw (GCS Parquet / BigQuery raw)
    ↓
Transform (dbt: staging → intermediate → marts)
    ↓
Quality (dbt tests + monitoring checks)
    ↓
Serve (FastAPI endpoints + BI dashboards)

Orchestrated by: Airflow (daily schedule)
Monitored by: monitoring/monitor.py (Slack alerts)
Governed by: data contracts + dbt docs
```
