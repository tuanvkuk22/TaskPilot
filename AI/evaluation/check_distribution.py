from collections import Counter
import pandas as pd

from utils import (
    read_csv_by_chunk,
    save_dataframe,
    print_title
)


def check_distribution(
        filename,
        column,
        top_n=None
):

    print_title(
        f"Distribution - {filename} - {column}"
    )

    counter = Counter()

    total = 0

    for chunk in read_csv_by_chunk(
            filename,
            usecols=[column]
    ):

        values = chunk[column].dropna()

        total += len(values)

        counter.update(values)

    data = []

    for value, count in counter.items():

        data.append({
            "Value": value,
            "Count": count,
            "Percent": round(
                count / total * 100,
                2
            )
        })

    result = pd.DataFrame(data)

    result = result.sort_values(
        by="Count",
        ascending=False
    )

    if top_n:
        result = result.head(top_n)

    save_dataframe(
        result,
        f"distribution_{filename}_{column}.csv"
    )

    print(result)


if __name__ == "__main__":

    check_distribution(
        "issues_clean.csv",
        "status"
    )