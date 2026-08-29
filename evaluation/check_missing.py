import pandas as pd

from utils import (
    read_csv_by_chunk,
    save_dataframe,
    print_title
)

def check_missing(filename):

    print_title(f"Checking Missing Values - {filename}")

    total_rows = 0
    missing = None
    columns = None

    for chunk in read_csv_by_chunk(filename):

        total_rows += len(chunk)

        if missing is None:

            columns = chunk.columns

            missing = pd.Series(
                0,
                index=columns,
                dtype="int64"
            )

        missing += chunk.isnull().sum()

    result = pd.DataFrame({
    "column": columns,
    "missing_count": missing.values,
    "missing_percent": (
        missing.values / total_rows * 100
    ).round(2)
})

    result = result.sort_values(
        by="missing_percent",
        ascending=False
    )

    save_dataframe(
        result,
        f"missing_{filename}"
    )

    print(result)
    print(f"\nTotal rows : {total_rows}")


if __name__ == "__main__":

    check_missing("issues_clean.csv")