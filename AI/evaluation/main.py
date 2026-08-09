from check_missing import check_missing
from check_duplicate import check_duplicate
from check_foreign_key import check_foreign_key
from check_distribution import check_distribution
from check_validation import check_validation
from feature_usage import run_feature_usage
from aggregate_report import run_aggregate

FILES = [
    ("issues_clean.csv", "_id"),
    ("events.csv", "id"),
    ("worklogs.csv", "id"),
    ("users.csv", "id")
]
def run_missing():

    print("\n========== CHECK MISSING ==========\n")

    for file, _ in FILES:
        check_missing(file)

def run_duplicate():
    print("\n========== CHECK DUPLICATE ==========\n")

    for file, pk in FILES:
        check_duplicate(file, pk)

def run_foreign_key():
    print("\n========== CHECK FOREIGN KEY ==========\n")

    relations = [
    (
        "events.csv",
        "issue",
        "issues_clean.csv",
        "_id"
    ),
    (
        "worklogs.csv",
        "issue",
        "issues_clean.csv",
        "_id"
    ),
    (
        "worklogs.csv",
        "issueId",
        "issues_clean.csv",
        "_id"
    ),
]

    for relation in relations:
        check_foreign_key(*relation)

def run_distribution():

    print("\n========== CHECK DISTRIBUTION ==========\n")

    distributions = [

        ("issues_clean.csv", "status"),
        ("issues_clean.csv", "priority"),
        ("issues_clean.csv", "issuetype"),
        ("issues_clean.csv", "projectname"),
        ("issues_clean.csv", "assignee"),

        ("events.csv", "author"),

        ("worklogs.csv", "author"),
        ("worklogs.csv", "updateAuthor"),

        ("users.csv", "active"),
        ("users.csv", "timeZone")
    ]

    for file, column in distributions:
        check_distribution(file, column)


def run_validation():

    print("\n========== CHECK VALIDATION ==========\n")

    check_validation()

def main():

    # run_missing()

    # run_duplicate()

    # run_foreign_key()

    # run_distribution()

    # run_validation()

    try:
        run_feature_usage()
        run_aggregate()
    except Exception as e:
        print(f"Warning: failed to run extended reports: {e}")

    print("\n===================================")
    print("Data Quality Assessment Completed.")
    print("All reports are saved in output/.")
    print("===================================")


if __name__ == "__main__":
    main()