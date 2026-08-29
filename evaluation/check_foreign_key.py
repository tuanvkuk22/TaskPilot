import pandas as pd

from utils import (
    read_csv_by_chunk,
    load_column_as_set,
    save_dataframe,
    print_title,
    check_foreign_key_via_partitions,
)
from config import TMP_DIR, PARTITION_THRESHOLD, PARTITION_COUNT
import os


def check_foreign_key(
    child_file,
    child_column,
    parent_file,
    parent_column
):

    print_title(
        f"{child_file}.{child_column} -> "
        f"{parent_file}.{parent_column}"
    )

    # decide strategy based on parent table size (use partitioning for large parents)
    from utils import count_rows

    parent_count_est = count_rows(parent_file)

    total = 0
    invalid = 0

    if parent_count_est > PARTITION_THRESHOLD:

        prefix = f"fk_{os.path.splitext(parent_file)[0]}_{parent_column}_{os.path.splitext(child_file)[0]}_{child_column}"
        total, invalid = check_foreign_key_via_partitions(parent_file, parent_column, child_file, child_column, TMP_DIR, prefix, nparts=PARTITION_COUNT)

    else:

        parent_keys = load_column_as_set(parent_file, parent_column)

        for chunk in read_csv_by_chunk(
            child_file,
            usecols=[child_column]
        ):

            values = chunk[child_column].dropna()

            total += len(values)

            invalid += (~values.isin(parent_keys)).sum()

    valid = total - invalid
    valid_percent = round(valid / total * 100, 2) if total > 0 else 0

    result = pd.DataFrame({
        "Child Table": [child_file],
        "Child Column": [child_column],
        "Parent Table": [parent_file],
        "Parent Column": [parent_column],
        "Checked": [total],
        "Valid FK": [valid],
        "Invalid FK": [invalid],
        "Valid (%)": [valid_percent]
    })

    save_dataframe(
        result,
        f"fk_{child_file.replace('.csv', '')}_{child_column}.csv"
    )

    print(result)


if __name__ == "__main__":

    check_foreign_key(
        "events.csv",
        "issue",
        "issues_clean.csv",
        "_id"
    )