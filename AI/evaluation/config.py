import os

BASE_DIR = os.path.dirname(
    os.path.dirname(
        os.path.abspath(__file__)
    )
)

DATASET_DIR = os.path.join(BASE_DIR, "dataset")

OUTPUT_DIR = os.path.join(BASE_DIR, "output")

LOG_DIR = os.path.join(BASE_DIR, "logs")

CHUNK_SIZE = 100000

CSV_ENGINE = "python"

# how to handle bad lines: 'skip' | 'log'
ON_BAD_LINES = "log"

# temp dir for disk-backed partition stores (must have free space)
TMP_DIR = os.path.join(BASE_DIR, "tmp")
os.makedirs(TMP_DIR, exist_ok=True)

# when number of rows exceeds this threshold, switch to partition-based checks
PARTITION_THRESHOLD = 200000

# number of partitions to use for on-disk hashing
PARTITION_COUNT = 1024

# Heuristics weights for feature usability scoring (sum not required)
HEURISTICS_WEIGHTS = {
    "completeness": 0.4,
    "uniqueness": 0.2,
    "cardinality": 0.1,
    "fk_valid": 0.3
}

os.makedirs(OUTPUT_DIR, exist_ok=True)
os.makedirs(LOG_DIR, exist_ok=True)