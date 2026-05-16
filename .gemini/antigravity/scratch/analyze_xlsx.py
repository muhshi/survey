import pandas as pd
import json

file_path = '/Users/saiful/development/survey/data_sipw_demak.xlsx'
df = pd.read_excel(file_path)

# Get columns
columns = df.columns.tolist()

# Get sample data (first 5 rows)
sample_data = df.head(5).to_dict(orient='records')

# Analyze unique values for potential dropdowns
analysis = {
    "columns": columns,
    "sample_rows": sample_data,
    "unique_counts": {
        col: df[col].nunique() for col in columns if df[col].nunique() < 100
    }
}

print(json.dumps(analysis, indent=2))
