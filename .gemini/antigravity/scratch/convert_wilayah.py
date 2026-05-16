import pandas as pd
import json
import os

file_path = '/Users/saiful/development/survey/data_sipw_demak.xlsx'
output_path = '/Users/saiful/development/survey/storage/app/wilayah.json'

# Ensure directory exists
os.makedirs(os.path.dirname(output_path), exist_ok=True)

print(f"Reading {file_path}...")
df = pd.read_excel(file_path)

# Replace NaN with None explicitly for all columns
df = df.astype(object).where(pd.notnull(df), None)

print(f"Converting to JSON...")
data = df.to_dict(orient='records')

with open(output_path, 'w') as f:
    json.dump(data, f)

print(f"Done! Saved to {output_path}")
