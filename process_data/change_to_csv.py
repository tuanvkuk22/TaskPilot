import gzip
import bson
import csv

input_file = "worklogs.bson.gz"
output_file = "worklogs.csv"

with gzip.open(input_file, "rb") as f:
    docs = bson.decode_file_iter(f)

    first_doc = next(docs)

    with open(output_file, "w", newline="", encoding="utf-8") as out:
        writer = csv.DictWriter(
            out,
            fieldnames=first_doc.keys(),
            extrasaction="ignore"
        )

        writer.writeheader()
        writer.writerow(first_doc)

        for doc in docs:
            writer.writerow(doc)

print("Done!")