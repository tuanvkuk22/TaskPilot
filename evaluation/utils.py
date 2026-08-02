import os
import csv
import sys
import pandas as pd
from config import (
    CHUNK_SIZE,
    BASE_DIR,
    DATASET_DIR,
    OUTPUT_DIR,
    CSV_ENGINE,
    ON_BAD_LINES,
    LOG_DIR,
    TMP_DIR,
    PARTITION_COUNT,
    PARTITION_THRESHOLD
)

csv.field_size_limit(sys.maxsize)

os.makedirs(OUTPUT_DIR, exist_ok=True)
# READ CSV
# ==========================

def read_csv_by_chunk(
        filename,
        usecols=None,
        dtype=None
):
    """
    Read large CSV file by chunks.
    """

    filepath = os.path.join(DATASET_DIR, filename)

    # support logging bad lines when ON_BAD_LINES == 'log'
    on_bad = ON_BAD_LINES

    if ON_BAD_LINES == "log":

        def _bad_line_handler(bad_line):
            try:
                line_str = ",".join(bad_line)
            except Exception:
                line_str = str(bad_line)

            log_path = os.path.join(LOG_DIR, "bad_lines.log")
            with open(log_path, "a", encoding="utf-8") as fh:
                fh.write(line_str + "\n")

            return None

        on_bad = _bad_line_handler

    return pd.read_csv(
        filepath,
        chunksize=CHUNK_SIZE,
        engine=CSV_ENGINE,
        on_bad_lines=on_bad,
        usecols=usecols,
        dtype=dtype
    )


# ==========================
# SAVE CSV
# ==========================

def save_dataframe(df, filename):

    path = os.path.join(
        OUTPUT_DIR,
        filename
    )

    df.to_csv(
        path,
        index=False,
        encoding="utf-8"
    )

    print(f"Saved -> {path}")


# ==========================
# PRINT TITLE
# ==========================

def print_title(title):

    print("\n")
    print("=" * 60)
    print(title)
    print("=" * 60)


# ==========================
# COUNT ROWS
# ==========================

def count_rows(filename):

    total = 0

    for chunk in read_csv_by_chunk(filename):
        total += len(chunk)

    return total


# ==========================
# GET COLUMN LIST
# ==========================

def get_columns(filename):

    filepath = os.path.join(
        DATASET_DIR,
        filename
    )

    df = pd.read_csv(
        filepath,
        nrows=1
    )

    return list(df.columns)


# ==========================
# LOAD COLUMN AS SET
# ==========================

def load_column_as_set(
        filename,
        column
):

    values = set()

    for chunk in read_csv_by_chunk(
            filename,
            usecols=[column]
    ):

        values.update(
            chunk[column].dropna()
        )

    return values


# ==========================
# PARTITION-BASED HELPERS (disk-backed, no sqlite)
# ==========================

def _ensure_dir(path):
    os.makedirs(path, exist_ok=True)


def _partition_path(prefix_dir, part_index):
    return os.path.join(prefix_dir, f"part_{part_index}.txt")


def partition_column_to_files(filename, column, tmp_dir, prefix, nparts=None):
    """Partition column values into nparts files under tmp_dir/prefix/part_<i>.txt.
       Returns the prefix directory path and total count written.
    """

    if nparts is None:
        nparts = PARTITION_COUNT

    prefix_dir = os.path.join(tmp_dir, prefix)
    _ensure_dir(prefix_dir)

    # open file handles
    files = [open(_partition_path(prefix_dir, i), "a", encoding="utf-8") for i in range(nparts)]

    total = 0

    for chunk in read_csv_by_chunk(filename, usecols=[column]):

        values = chunk[column].dropna().astype(str).tolist()

        for v in values:
            idx = (hash(v) & 0x7FFFFFFF) % nparts
            files[idx].write(v.replace("\n", " ") + "\n")
            total += 1

    for f in files:
        f.close()

    return prefix_dir, total


def count_duplicates_from_partitions(prefix_dir, nparts=None):
    if nparts is None:
        nparts = PARTITION_COUNT

    dup_count = 0
    total = 0

    for i in range(nparts):
        path = _partition_path(prefix_dir, i)
        if not os.path.exists(path):
            continue

        seen = set()
        with open(path, "r", encoding="utf-8") as fh:
            for line in fh:
                v = line.rstrip("\n")
                total += 1
                if v in seen:
                    dup_count += 1
                else:
                    seen.add(v)

    return total, dup_count


def check_foreign_key_via_partitions(parent_file, parent_col, child_file, child_col, tmp_dir, prefix, nparts=None):
    """Partition parent and child and count invalid FK in child (values not in parent).
       Returns (checked_count, invalid_count).
    """
    if nparts is None:
        nparts = PARTITION_COUNT

    parent_prefix_dir, parent_total = partition_column_to_files(parent_file, parent_col, tmp_dir, prefix + "_parent", nparts)
    child_prefix_dir, child_total = partition_column_to_files(child_file, child_col, tmp_dir, prefix + "_child", nparts)

    invalid = 0
    checked = 0

    for i in range(nparts):
        ppath = _partition_path(parent_prefix_dir, i)
        cpath = _partition_path(child_prefix_dir, i)

        if not os.path.exists(cpath):
            continue

        parent_set = set()
        if os.path.exists(ppath):
            with open(ppath, "r", encoding="utf-8") as pf:
                for line in pf:
                    parent_set.add(line.rstrip("\n"))

        with open(cpath, "r", encoding="utf-8") as cf:
            for line in cf:
                v = line.rstrip("\n")
                checked += 1
                if v not in parent_set:
                    invalid += 1

    return checked, invalid


# ==========================
# FORMAT %
# ==========================

def percent(a, b):

    if b == 0:
        return 0

    return round(
        a / b * 100,
        2
    )