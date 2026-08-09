import math
from collections import Counter
import json

from utils import (
    read_csv_by_chunk,
    save_dataframe,
    print_title,
    get_columns,
    count_rows,
    percent
)

SAMPLE_SIZE = 100000
UNIQUE_LIMIT = 200000


def is_number(x):
    try:
        float(x)
        return True
    except Exception:
        return False


class RunningStats:
    def __init__(self):
        self.n = 0
        self.mean = 0.0
        self.M2 = 0.0
        self.min = None
        self.max = None

    def add(self, x):
        x = float(x)
        self.n += 1
        if self.min is None or x < self.min:
            self.min = x
        if self.max is None or x > self.max:
            self.max = x
        delta = x - self.mean
        self.mean += delta / self.n
        delta2 = x - self.mean
        self.M2 += delta * delta2

    def variance(self):
        return self.M2 / (self.n - 1) if self.n > 1 else 0.0

    def std(self):
        return math.sqrt(self.variance())


def analyze_file(filename, target_column=None, top_n=10):

    print_title(f"Feature Analysis - {filename}")

    cols = get_columns(filename)
    total_rows = count_rows(filename)

    results = []

    # sample for mutual information / missingness-target correlation
    sample_rows = []

    for chunk in read_csv_by_chunk(filename):
        if len(sample_rows) < SAMPLE_SIZE:
            sample_rows.extend(chunk.to_dict(orient="records"))
        else:
            break

    for col in cols:

        non_null = 0
        unique_values = set()
        unique_truncated = False
        counter = Counter()

        num_stats = RunningStats()
        numeric_count = 0

        missing_with_target = 0
        total_checked = 0

        for chunk in read_csv_by_chunk(filename, usecols=[col] + ([target_column] if target_column else [])):
            values = chunk[col]

            non_null += values.notna().sum()

            # top values
            counter.update(values.dropna())

            # unique
            for v in values.dropna().astype(str):
                unique_values.add(v)
                if len(unique_values) > UNIQUE_LIMIT:
                    unique_truncated = True
                    break
            if unique_truncated:
                # stop collecting uniques
                pass

            # numeric stats
            for v in values.dropna():
                if is_number(v):
                    try:
                        num_stats.add(v)
                        numeric_count += 1
                    except Exception:
                        pass

            # missingness correlation with target (sample-based)
            if target_column:
                t = chunk[target_column]
                for a, b in zip(values.isna(), t):
                    total_checked += 1
                    if a and (not pd_isna(b)):
                        missing_with_target += 1

        completeness = percent(non_null, total_rows)

        unique_count = len(unique_values) if not unique_truncated else None
        uniqueness_ratio = round(unique_count / non_null * 100, 2) if unique_count and non_null > 0 else None

        top_values = counter.most_common(top_n)

        cardinality = "unknown"
        if unique_count is None:
            cardinality = "very_high"
        else:
            if unique_count <= 20:
                cardinality = "low"
            elif unique_count <= 1000:
                cardinality = "medium"
            else:
                cardinality = "high"

        # numeric summary
        numeric_mean = num_stats.mean if num_stats.n > 0 else None
        numeric_std = num_stats.std() if num_stats.n > 1 else None
        numeric_min = num_stats.min
        numeric_max = num_stats.max

        # missingness correlation with target (rough)
        missing_target_corr = None
        if target_column and total_checked > 0:
            missing_target_corr = round(missing_with_target / total_checked * 100, 2)

        # mutual information with target (sample-based)
        mi = None
        if target_column and sample_rows:
            try:
                from sklearn.feature_selection import mutual_info_classif, mutual_info_regression
                import numpy as np
                # build arrays
                xs = []
                ys = []
                for r in sample_rows:
                    if col in r and target_column in r:
                        x = r.get(col)
                        y = r.get(target_column)
                        if x is None or y is None:
                            continue
                        xs.append(x)
                        ys.append(y)

                if xs and ys:
                    # encode categorical features
                    def encode_array(arr):
                        try:
                            arr = [float(x) for x in arr]
                            return np.array(arr, dtype=float), True
                        except Exception:
                            # factorize
                            vals, _ = pd_factorize(arr)
                            return np.array(vals, dtype=float), False

                    x_enc, x_is_num = encode_array(xs)
                    y_enc, y_is_num = encode_array(ys)

                    if y_is_num:
                        # regression MI
                        mi_val = mutual_info_regression(x_enc.reshape(-1, 1), y_enc, random_state=0)
                        mi = float(mi_val[0])
                    else:
                        mi_val = mutual_info_classif(x_enc.reshape(-1, 1), y_enc, discrete_features=not x_is_num, random_state=0)
                        mi = float(mi_val[0])
            except Exception:
                mi = None

        results.append({
            "column": col,
            "total_rows": total_rows,
            "non_null": non_null,
            "completeness(%)": completeness,
            "unique_count": unique_count if unique_count is not None else "<truncated>",
            "uniqueness_ratio(%)": uniqueness_ratio,
            "cardinality": cardinality,
            "top_values": json.dumps(top_values, ensure_ascii=False),
            "numeric_mean": numeric_mean,
            "numeric_std": numeric_std,
            "numeric_min": numeric_min,
            "numeric_max": numeric_max,
            "missing_target_corr(%)": missing_target_corr,
            "mutual_info_with_target": mi
        })

    df = __import__("pandas").DataFrame(results)

    save_dataframe(df, f"feature_analysis_{filename}")

    print(df)


def pd_isna(x):
    return x is None or (isinstance(x, float) and math.isnan(x))


def pd_factorize(arr):
    # simple factorize
    mapping = {}
    out = []
    i = 0
    for v in arr:
        if v not in mapping:
            mapping[v] = i
            i += 1
        out.append(mapping[v])
    return out, mapping


def run_feature_analysis(files=None, target=None):
    if files is None:
        files = ["issues_clean.csv", "events.csv", "worklogs.csv", "users.csv"]

    for f in files:
        analyze_file(f, target_column=target)


if __name__ == "__main__":
    run_feature_analysis()
