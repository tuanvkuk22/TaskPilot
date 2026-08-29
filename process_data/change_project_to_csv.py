import gzip
import pandas as pd
from bson import decode_iter
from bs4 import BeautifulSoup

print("Loading projects.bson.gz ...")

with gzip.open("projects.bson.gz", "rb") as f:
    bson_bytes = f.read()

data = list(decode_iter(bson_bytes))

print(f"Loaded {len(data)} projects")

df = pd.DataFrame(data)

keep_cols = [
    "id",
    "key",
    "name",
    "description",
    "projectTypeKey",
    "projectCategory",
    "lead",
    "components",
    "issueTypes",
    "versions",
    "archived",
    "lastcrawled"
]

df = df[[c for c in keep_cols if c in df.columns]]


def clean_html(text):
    if pd.isna(text):
        return ""

    return BeautifulSoup(
        str(text),
        "html.parser"
    ).get_text(" ", strip=True)


df["description"] = df["description"].apply(clean_html)


# Category -> chỉ lấy tên
df["projectCategory"] = df["projectCategory"].apply(
    lambda x: x.get("name", "")
    if isinstance(x, dict)
    else ""
)


# Lead -> displayName
df["lead"] = df["lead"].apply(
    lambda x: x.get("displayName", "")
    if isinstance(x, dict)
    else ""
)


# Components
df["component_count"] = df["components"].apply(
    lambda x: len(x) if isinstance(x, list) else 0
)

df["components"] = df["components"].apply(
    lambda x: ";".join(
        comp.get("name", "")
        for comp in x
    ) if isinstance(x, list) else ""
)


# Issue Types
df["issue_type_count"] = df["issueTypes"].apply(
    lambda x: len(x) if isinstance(x, list) else 0
)

df["issueTypes"] = df["issueTypes"].apply(
    lambda x: ";".join(
        item.get("name", "")
        for item in x
    ) if isinstance(x, list) else ""
)


# Versions
df["version_count"] = df["versions"].apply(
    lambda x: len(x) if isinstance(x, list) else 0
)

df["versions"] = df["versions"].apply(
    lambda x: ";".join(
        v.get("name", "")
        for v in x
    ) if isinstance(x, list) else ""
)


df.to_csv(
    "project_clean.csv",
    index=False,
    encoding="utf-8"
)

print(df.head())
print("\nSaved:", len(df))