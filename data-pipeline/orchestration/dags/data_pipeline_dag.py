"""
Step 6: SCHEDULE — Airflow DAG that orchestrates the full pipeline
Extract → Upload → dbt Transform → dbt Test → Notify
"""

from datetime import datetime, timedelta
from airflow import DAG
from airflow.operators.python import PythonOperator
from airflow.operators.bash import BashOperator
from airflow.operators.empty import EmptyOperator
from airflow.utils.trigger_rule import TriggerRule

DBT_DIR = "/opt/dbt/data_pipeline"  # Change to your dbt project path

default_args = {
    "owner": "data-engineering",
    "depends_on_past": False,
    "email_on_failure": True,
    "email": ["data-team@yourcompany.com"],  # ← Replace
    "retries": 2,
    "retry_delay": timedelta(minutes=5),
    "execution_timeout": timedelta(hours=2),
}

dag = DAG(
    dag_id="data_pipeline_daily",
    default_args=default_args,
    description="Daily ELT: Extract → Load → Transform → Test → Notify",
    schedule_interval="0 6 * * *",  # Daily at 6 AM UTC
    start_date=datetime(2024, 1, 1),
    catchup=False,
    max_active_runs=1,
    tags=["production", "elt"],
)


def run_extraction(**ctx):
    """Step 1: Extract data from APIs."""
    from extraction.extract_api import APIExtractor, save_raw
    import os
    base_url = os.getenv("API_BASE_URL", "https://jsonplaceholder.typicode.com")
    extractor = APIExtractor(base_url)
    for endpoint in ["users", "posts"]:
        data = extractor.extract(endpoint)
        save_raw(data, source_name=f"api_{endpoint}")


def run_upload(**ctx):
    """Step 2: Upload raw files to GCS."""
    from loading.upload_to_gcs import upload_all
    upload_all()


def run_monitoring(**ctx):
    """Step 8: Run quality monitoring checks."""
    from monitoring.monitor import PipelineMonitor
    import os
    project = os.getenv("GCP_PROJECT_ID")
    if not project:
        print("⚠️ GCP_PROJECT_ID not set, skipping monitoring")
        return
    monitor = PipelineMonitor(project)
    monitor.check_freshness("analytics", "fct_orders", max_hours=24)
    monitor.check_row_count("analytics", "fct_orders", min_rows=100)
    summary = monitor.get_summary()
    if summary["failures"] > 0:
        raise Exception(f"{summary['failures']} monitoring checks failed!")


def notify_success(ctx):
    print(f"✅ Pipeline succeeded for {ctx['ds']}")


def notify_failure(ctx):
    task = ctx.get("task_instance")
    print(f"🔴 FAILED: {task.dag_id}.{task.task_id} on {ctx['ds']}")
    print(f"   Error: {ctx.get('exception', 'Unknown')}")


with dag:
    start = EmptyOperator(task_id="start")

    extract = PythonOperator(
        task_id="extract_data",
        python_callable=run_extraction,
    )

    upload = PythonOperator(
        task_id="upload_to_gcs",
        python_callable=run_upload,
    )

    dbt_deps = BashOperator(
        task_id="dbt_deps",
        bash_command=f"cd {DBT_DIR} && dbt deps",
    )

    dbt_run = BashOperator(
        task_id="dbt_run",
        bash_command=f"cd {DBT_DIR} && dbt run --target prod",
    )

    dbt_test = BashOperator(
        task_id="dbt_test",
        bash_command=f"cd {DBT_DIR} && dbt test --target prod",
    )

    monitor = PythonOperator(
        task_id="run_monitoring",
        python_callable=run_monitoring,
    )

    success = PythonOperator(
        task_id="notify_success",
        python_callable=notify_success,
        trigger_rule=TriggerRule.ALL_SUCCESS,
    )

    failure = PythonOperator(
        task_id="notify_failure",
        python_callable=notify_failure,
        trigger_rule=TriggerRule.ONE_FAILED,
    )

    end = EmptyOperator(
        task_id="end",
        trigger_rule=TriggerRule.NONE_FAILED_MIN_ONE_SUCCESS,
    )

    # DAG Flow:
    # start → extract → upload → dbt_deps → dbt_run → dbt_test → monitor → success → end
    #                                                                      ↘ failure ↗
    start >> extract >> upload >> dbt_deps >> dbt_run >> dbt_test >> monitor
    monitor >> success >> end
    monitor >> failure >> end
