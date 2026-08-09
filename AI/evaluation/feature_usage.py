import json
from collections import Counter

import pandas as pd

from utils import (
    read_csv_by_chunk,
    save_dataframe,
    print_title,
    get_columns,
    count_rows,
    percent,
    load_column_as_set
)

from check_foreign_key import check_foreign_key


UNIQUE_LIMIT = 200000


# ==========================================
# AI ROLE
# ==========================================

FEATURE_COLUMNS = {
    "summary",
    "description",
    "priority",
    "status",
    "issuetype",
    "labels",
    "components",
    "fixVersions"
}

TARGET_COLUMNS = {
    "assignee"
}

KEY_COLUMNS = {
    "_id",
    "id",
    "issue",
    "issueId"
}

IGNORE_COLUMNS = {
    "avatarUrls",
    "self"
}


def get_ai_role(column):

    if column in FEATURE_COLUMNS:
        return "Feature"

    if column in TARGET_COLUMNS:
        return "Target"

    if column in KEY_COLUMNS:
        return "Key"

    if column in IGNORE_COLUMNS:
        return "Ignore"

    return "Optional"


# ==========================================
# RECOMMENDATION
# ==========================================

def get_recommendation(role, completeness):

    if role in ("Feature", "Target", "Key"):
        return "Keep"

    if role == "Ignore":
        return "Ignore"

    if completeness < 20:
        return "Optional"

    return "Keep"


# ==========================================
# UTILITY SCORE
# ==========================================

def utility_score(role, completeness, fk_percent):

    score = completeness

    if role == "Feature":
        score += 30

    elif role == "Target":
        score += 35

    elif role == "Key":
        score += 15

    elif role == "Ignore":
        score -= 40

    if fk_percent is not None:
        score += fk_percent * 0.1

    return min(100, round(score, 2))


# ==========================================
# FK MAP
# ==========================================

FK_MAP = {
    "issue": ("issues_clean.csv", "_id"),
    "issueId": ("issues_clean.csv", "_id"),
    "author": ("users.csv", "id"),
    "assignee": ("users.csv", "id"),
    "reporter": ("users.csv", "id"),
    "updateAuthor": ("users.csv", "id")
}


# ==========================================
# MAIN
# ==========================================

def assess_file(filename, top_n=5):

    print_title(f"Feature Usage - {filename}")
    table = filename.replace(".csv", "")
    columns = get_columns(filename)
    total_rows = count_rows(filename)
    results = []

    for column in columns:

        non_null = 0
        unique_values = set()
        truncated = False
        counter = Counter()
        dtype = None

        for chunk in read_csv_by_chunk(
                filename,
                usecols=[column]
        ):

            if dtype is None:
                dtype = str(chunk[column].dtype)

            values = chunk[column].dropna()
            non_null += len(values)
            counter.update(values)

            if not truncated:

                for value in values:
                    unique_values.add(value)

                    if len(unique_values) >= UNIQUE_LIMIT:
                        truncated = True
                        break

        completeness = percent(
            non_null,
            total_rows
        )

        if truncated:
            unique_count = None
            uniqueness = None
        else:
            unique_count = len(unique_values)
            uniqueness = percent(
                unique_count,
                non_null
            ) if non_null else 0

        top_values = "; ".join(
            f"{k}:{v}"
            for k, v in counter.most_common(top_n)
        )

        fk_valid = None

        if column in FK_MAP:
            parent_file, parent_col = FK_MAP[column]
            parent_keys = load_column_as_set(
                parent_file,
                parent_col
            )

            total = 0
            invalid = 0

            for chunk in read_csv_by_chunk(
                    filename,
                    usecols=[column]
            ):

                values = chunk[column].dropna()
                total += len(values)

                invalid += (
                    ~values.isin(parent_keys)
                ).sum()

            fk_valid = percent(
                total-invalid,
                total
            )

        role = get_ai_role(column)

        recommendation = get_recommendation(
            role,
            completeness
        )

        score = utility_score(
            role,
            completeness,
            fk_valid
        )

        results.append({

            "table": table,
            "column": column,
            "dtype": dtype,
            "total_rows": total_rows,
            "non_null": non_null,
            "completeness(%)": completeness,
            "unique_count": unique_count if unique_count is not None else ">200000",
            "uniqueness_ratio(%)": uniqueness,
            "high_cardinality": truncated,
            "top_values": top_values,
            "fk_valid_percent": fk_valid,
            "ai_role": role,
            "recommendation": recommendation,
            "utility_score": score
        })

    df = pd.DataFrame(results)

    save_dataframe(
        df,
        f"feature_usage_{filename}"
    )

    print(df)


def run_feature_usage():

    files = [
        "issues_clean.csv",
        "events.csv",
        "worklogs.csv",
        "users.csv"
    ]

    for file in files:
        assess_file(file)

if __name__ == "__main__":
    run_feature_usage()