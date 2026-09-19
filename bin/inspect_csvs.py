import csv
import glob

csv_files = glob.glob("data/*.csv")
for f in csv_files:
    with open(f, 'r', encoding='utf-8', errors='ignore') as fp:
        reader = csv.reader(fp)
        try:
            header = next(reader)
            row_count = sum(1 for _ in reader)
            print(f"{f}: {row_count} rows | Header: {header[:10]}...")
        except Exception as e:
            print(f"Error reading {f}: {e}")
