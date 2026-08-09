import gzip
import re
import pandas as pd
from bson import decode_file_iter

INPUT_FILE = "issues.bson.gz"
OUTPUT_FILE = "issues_clean.csv"
CHUNK_SIZE = 5000

KEEP_FIELDS = [
    "_id",
    "key",
    "projectname",
    "summary",
    "description",
    "issuetype",
    "priority",
    "status",
    "resolution",
    "assignee",
    "reporter",
    "created",
    "updated",
    "resolutiondate",
    "duedate",
    "parent",
    "issuelinks",
    "components",
    "labels",
    "fixVersions",
    "attachmentcount",
    "votes",
    "watches",
    "timeestimate",
    "timeoriginalestimate",
    "timespent",
    "workratio"
]


def clean_text(text):
    if text is None:
        return ""

    text = str(text)
    text = re.sub(r"<[^>]+>", " ", text)
    text = re.sub(r"\s+", " ", text)

    return text.strip()


def get_name(obj):
    if isinstance(obj, dict):
        return obj.get("name", "")
    return obj


def get_names(items):
    if not isinstance(items, list):
        return ""

    names = []

    for item in items:
        if isinstance(item, dict):
            names.append(str(item.get("name", "")))
        else:
            names.append(str(item))

    return "|".join(filter(None, names))


def get_vote_count(v):
    if isinstance(v, dict):
        return v.get("votes", 0)
    return v


def get_watch_count(v):
    if isinstance(v, dict):
        return v.get("watchCount", 0)
    return v


def get_parent_key(parent):
    if isinstance(parent, dict):
        return parent.get("key", "")
    return ""


def get_issue_links(links):
    """
    Trả về dạng:
    blocks:JELLY-2|is blocked by:JELLY-3
    """

    if not isinstance(links, list):
        return ""

    result = []

    for link in links:

        if not isinstance(link, dict):
            continue

        link_type = link.get("type", {})

        outward = link.get("outwardIssue")
        inward = link.get("inwardIssue")

        if outward:
            relation = link_type.get("outward", "")
            target = outward.get("key", "")
            result.append(f"{relation}:{target}")

        elif inward:
            relation = link_type.get("inward", "")
            target = inward.get("key", "")
            result.append(f"{relation}:{target}")

    return "|".join(result)


rows = []
first_chunk = True

print("Processing BSON...")

with gzip.open(INPUT_FILE, "rb") as f:

    for idx, doc in enumerate(decode_file_iter(f), start=1):

        row = {}

        for field in KEEP_FIELDS:

            value = doc.get(field)

            if field == "description":
                row[field] = clean_text(value)

            elif field == "parent":
                row["parent_key"] = get_parent_key(value)

            elif field == "issuelinks":
                row["issue_links"] = get_issue_links(value)

            elif field in [
                "issuetype",
                "priority",
                "status",
                "resolution"
            ]:
                row[field] = get_name(value)

            elif field in [
                "components",
                "labels",
                "fixVersions"
            ]:
                row[field] = get_names(value)

            elif field == "votes":
                row[field] = get_vote_count(value)

            elif field == "watches":
                row[field] = get_watch_count(value)

            else:
                row[field] = value

        rows.append(row)

        if len(rows) >= CHUNK_SIZE:

            pd.DataFrame(rows).to_csv(
                OUTPUT_FILE,
                mode="w" if first_chunk else "a",
                header=first_chunk,
                index=False
            )

            print(f"Saved {idx} issues")

            rows.clear()
            first_chunk = False

    if rows:

        pd.DataFrame(rows).to_csv(
            OUTPUT_FILE,
            mode="w" if first_chunk else "a",
            header=first_chunk,
            index=False
        )

print("Done:", OUTPUT_FILE)