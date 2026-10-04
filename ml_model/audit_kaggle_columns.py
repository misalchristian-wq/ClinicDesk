"""Read Kaggle source archives without unpacking or copying learner-level rows."""

import csv
import io
import sys
import zipfile
from collections import Counter


def inspect_archive(path):
    print(f"\n{path}")
    with zipfile.ZipFile(path) as archive:
        for name in archive.namelist():
            if not name.lower().endswith(".csv"):
                continue
            with archive.open(name) as raw:
                reader = csv.DictReader(io.TextIOWrapper(raw, encoding="utf-8-sig", errors="replace", newline=""))
                columns = reader.fieldnames or []
                blanks = {column: 0 for column in columns}
                examples = {column: set() for column in columns}
                age_column = next((column for column in columns if column.lower() == "age"), None)
                target_column = next((column for column in columns if column.lower() in ("predicted deficiency", "disease_diagnosis")), None)
                age_min, age_max, school_age_count = None, None, 0
                targets = Counter()
                rows = 0
                for row in reader:
                    rows += 1
                    if age_column:
                        try:
                            age = float(row[age_column])
                            age_min = age if age_min is None else min(age_min, age)
                            age_max = age if age_max is None else max(age_max, age)
                            school_age_count += 5 <= age <= 19
                        except (TypeError, ValueError):
                            pass
                    if target_column:
                        targets[row[target_column]] += 1
                    for column in columns:
                        value = (row[column] or "").strip()
                        if value == "":
                            blanks[column] += 1
                        elif len(examples[column]) < 6:
                            examples[column].add(value[:80])
                print(f"  {name}: {rows} rows, {len(columns)} columns")
                if age_column:
                    print(f"  age range: {age_min}-{age_max}; ages 5-19: {school_age_count}/{rows}")
                if target_column:
                    print(f"  {target_column} counts: {dict(targets)}")
                for column in columns:
                    print(f"    {column}: blank={blanks[column]}/{rows}, examples={sorted(examples[column])}")


if __name__ == "__main__":
    for filename in sys.argv[1:]:
        inspect_archive(filename)
