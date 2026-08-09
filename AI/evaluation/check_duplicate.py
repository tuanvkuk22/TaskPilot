import pandas as pd
import os

from utils import (
    read_csv_by_chunk,
    save_dataframe,
    print_title
)


def check_duplicate(filename, primary_key):

    print_title(f"Checking Duplicate - {filename}")

    total_rows = 0
    duplicate_rows = 0

    duplicate_pk = 0

    # If dataset is large, use partitioned on-disk approach to track primary keys
    from utils import count_rows, partition_column_to_files, count_duplicates_from_partitions
    from config import TMP_DIR, PARTITION_THRESHOLD, PARTITION_COUNT

    total_est = count_rows(filename)

    if total_est > PARTITION_THRESHOLD:

        # compute chunk-level duplicate_rows while iterating once
        for chunk in read_csv_by_chunk(filename):
            total_rows += len(chunk)
            duplicate_rows += chunk.duplicated().sum()

        prefix = f"seen_{os.path.splitext(filename)[0]}_{primary_key}"
        prefix_dir, _ = partition_column_to_files(filename, primary_key, TMP_DIR, prefix, nparts=PARTITION_COUNT)

        _, duplicate_pk = count_duplicates_from_partitions(prefix_dir, nparts=PARTITION_COUNT)

    else:

        seen = set()

        for chunk in read_csv_by_chunk(filename):

            total_rows += len(chunk)

            # Duplicate record trong chunk
            duplicate_rows += chunk.duplicated().sum()

            # Duplicate primary key toàn bộ dataset
            for value in chunk[primary_key].dropna():

                if value in seen:
                    duplicate_pk += 1
                else:
                    seen.add(value)

    result = pd.DataFrame({
        "Metric": [
            "Total Rows",
            "Duplicate Records (Chunk)",
            "Duplicate Primary Key"
        ],
        "Value": [
            total_rows,
            duplicate_rows,
            duplicate_pk
        ]
    })

    save_dataframe(
        result,
        f"duplicate_{filename}"
    )

    print(result)


if __name__ == "__main__":

    check_duplicate(
        "issues_clean.csv",
        "id"
    )