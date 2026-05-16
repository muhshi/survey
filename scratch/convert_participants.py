import pandas as pd
import json
import os

def convert_excel_to_json(excel_file, json_file):
    if not os.path.exists(excel_file):
        print(f"Error: {excel_file} not found.")
        return

    # Read Excel
    # Use dtype=str for NIK and other ID-like fields to prevent scientific notation
    df = pd.read_excel(excel_file, dtype={
        'No Peserta': str,
        'NIK': str,
        'noHP': str
    })

    # Clean NIK (remove leading single quote if present)
    df['NIK'] = df['NIK'].str.replace("'", "", regex=False)
    
    # Map columns to internal keys
    # ['No Peserta', 'NIK', 'nama', 'email', 'noHP', 'pilihan_kec', 'pilihan_desa', 'Match Method']
    data = []
    for _, row in df.iterrows():
        participant = {
            'nomor_urut': str(row['No Peserta']),
            'nip': str(row['NIK']),
            'name': str(row['nama']),
            'email': str(row['email']) if pd.notna(row['email']) else None,
            'nomor_hp': str(row['noHP']) if pd.notna(row['noHP']) else None,
            'kecamatan': str(row['pilihan_kec']) if pd.notna(row['pilihan_kec']) else None,
            'desa': str(row['pilihan_desa']) if pd.notna(row['pilihan_desa']) else None,
        }
        data.append(participant)

    with open(json_file, 'w', encoding='utf-8') as f:
        json.dump(data, f, indent=4, ensure_ascii=False)
    
    print(f"Successfully converted {len(data)} participants to {json_file}")

if __name__ == "__main__":
    convert_excel_to_json('Data Peserta Wawancara SE2026.xlsx', 'participants.json')
