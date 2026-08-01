import glob
import os

import pandas as pd

from config import OUTPUT_DIR
from utils import print_title


def run_aggregate():

    print_title("Aggregate DQA Report")

    files = glob.glob(
        os.path.join(
            OUTPUT_DIR,
            "feature_usage_*.csv"
        )
    )

    if not files:
        print("No feature usage report found.")
        return

    dfs = []

    for file in files:
        dfs.append(pd.read_csv(file))

    df = pd.concat(
        dfs,
        ignore_index=True
    )

    # ==============================
    # Summary
    # ==============================

    summary = df[[
        "table",
        "column",
        "dtype",
        "completeness(%)",
        "unique_count",
        "fk_valid_percent",
        "ai_role",
        "recommendation",
        "utility_score"
    ]]

    summary = summary.sort_values(
        by=[
            "table",
            "utility_score"
        ],
        ascending=[
            True,
            False
        ]
    )

    summary_path = os.path.join(
        OUTPUT_DIR,
        "dqa_feature_summary.csv"
    )

    summary.to_csv(
        summary_path,
        index=False
    )

    print(f"Saved -> {summary_path}")

    # ==============================
    # Markdown Report
    # ==============================

    report = []

    report.append("# Data Quality Assessment Report\n")

    report.append(
        f"## Total Tables: {df['table'].nunique()}"
    )

    report.append(
        f"## Total Features: {len(df)}\n"
    )

    # ------------------------------

    report.append("## AI Readiness")

    overall = round(
        df["utility_score"].mean(),
        2
    )

    report.append(
        f"- Overall Score: **{overall}/100**"
    )

    if overall >= 80:
        report.append(
            "- Status: Ready for AI"
        )

    elif overall >= 60:
        report.append(
            "- Status: Need minor preprocessing"
        )

    else:
        report.append(
            "- Status: Need data cleaning"
        )

    report.append("")

    # ------------------------------

    report.append("## Keep")

    keep = df[
        df["recommendation"] == "Keep"
    ]

    for _, row in keep.iterrows():

        report.append(
            f"- {row['table']}.{row['column']} "
            f"(score={row['utility_score']})"
        )

    report.append("")

    # ------------------------------

    report.append("## Target")

    target = df[
        df["ai_role"] == "Target"
    ]

    for _, row in target.iterrows():

        report.append(
            f"- {row['table']}.{row['column']}"
        )

    report.append("")

    # ------------------------------

    report.append("## Optional")

    optional = df[
        df["recommendation"] == "Optional"
    ]

    for _, row in optional.iterrows():

        report.append(
            f"- {row['table']}.{row['column']}"
        )

    report.append("")

    # ------------------------------

    report.append("## Ignore")

    ignore = df[
        df["recommendation"] == "Ignore"
    ]

    for _, row in ignore.iterrows():

        report.append(
            f"- {row['table']}.{row['column']}"
        )

    report.append("")

    # ------------------------------

    report.append("## Low Completeness (<50%)")

    low = df[
        df["completeness(%)"] < 50
    ]

    for _, row in low.iterrows():

        report.append(
            f"- {row['table']}.{row['column']} "
            f"({row['completeness(%)']}%)"
        )

    report.append("")

    # ------------------------------

    fk = df[
        df["fk_valid_percent"].notna()
    ]

    if not fk.empty:

        report.append("## Foreign Key")

        for _, row in fk.iterrows():

            report.append(
                f"- {row['table']}.{row['column']} "
                f"({row['fk_valid_percent']}%)"
            )

        report.append("")

    report_path = os.path.join(
        OUTPUT_DIR,
        "dqa_report.md"
    )

    with open(
        report_path,
        "w",
        encoding="utf-8"
    ) as file:

        file.write(
            "\n".join(report)
        )

    print(f"Saved -> {report_path}")


if __name__ == "__main__":
    run_aggregate()