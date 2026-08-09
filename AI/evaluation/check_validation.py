import pandas as pd

from utils import (
    read_csv_by_chunk,
    save_dataframe,
    print_title
)


def check_validation():

    results = []

    # ==========================================
    # Worklog
    # ==========================================

    print_title("Checking Worklog")

    total = 0
    githubbot = 0
    jira_bot = 0
    invalid_time = 0

    for chunk in read_csv_by_chunk(
            "worklogs.csv",
            usecols=[
                "author",
                "timeSpentSeconds"
            ]
    ):

        total += len(chunk)

        githubbot += (
            chunk["author"] == "githubbot"
        ).sum()

        jira_bot += (
            chunk["author"] == "jira-bot"
        ).sum()

        invalid_time += (
            chunk["timeSpentSeconds"] <= 0
        ).sum()

    results.append({
        "Table": "worklogs",
        "Rule": "githubbot",
        "Count": githubbot
    })

    results.append({
        "Table": "worklogs",
        "Rule": "jira-bot",
        "Count": jira_bot
    })

    results.append({
        "Table": "worklogs",
        "Rule": "timeSpentSeconds <= 0",
        "Count": invalid_time
    })

    # ==========================================
    # Issue
    # ==========================================

    print_title("Checking Issue")

    no_assignee = 0
    no_summary = 0

    for chunk in read_csv_by_chunk(
            "issues_clean.csv",
            usecols=[
                "assignee",
                "summary"
            ]
    ):

        no_assignee += (
            chunk["assignee"].isna()
        ).sum()

        no_summary += (
            chunk["summary"].isna()
        ).sum()

    results.append({
        "Table": "issues",
        "Rule": "assignee is NULL",
        "Count": no_assignee
    })

    results.append({
        "Table": "issues",
        "Rule": "summary is NULL",
        "Count": no_summary
    })

    # ==========================================
    # User
    # ==========================================

    print_title("Checking User")

    inactive = 0

    for chunk in read_csv_by_chunk(
            "users.csv",
            usecols=["active"]
    ):

        inactive += (
            chunk["active"] == False
        ).sum()

    results.append({
        "Table": "users",
        "Rule": "inactive user",
        "Count": inactive
    })

    result = pd.DataFrame(results)

    save_dataframe(
        result,
        "outlier_report.csv"
    )

    print(result)


if __name__ == "__main__":

    check_validation()