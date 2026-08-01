import gzip
import bson
import csv

input_file = "events.bson.gz"
output_file = "events.csv"

fieldnames = [
    "_id",
    "author",
    "created",
    "id",
    "issue",
    "projectname",
    "field",
    "fieldtype",
    "from",
    "fromString",
    "to",
    "toString"
]

with gzip.open(input_file, "rb") as f, \
     open(output_file, "w", newline="", encoding="utf-8") as out:

    writer = csv.DictWriter(out, fieldnames=fieldnames)
    writer.writeheader()

    for doc in bson.decode_file_iter(f):

        base = {
            "_id": doc.get("_id"),
            "author": doc.get("author"),
            "created": doc.get("created"),
            "id": doc.get("id"),
            "issue": doc.get("issue"),
            "projectname": doc.get("projectname")
        }

        items = doc.get("items", [])

        # nếu không có items vẫn ghi 1 dòng
        if not items:
            writer.writerow(base)
            continue

        for item in items:
            row = base.copy()

            row["field"] = item.get("field")
            row["fieldtype"] = item.get("fieldtype")
            row["from"] = item.get("from")
            row["fromString"] = item.get("fromString")
            row["to"] = item.get("to")
            row["toString"] = item.get("toString")

            writer.writerow(row)

print("Done!")