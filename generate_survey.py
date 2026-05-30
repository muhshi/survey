import pandas as pd
import json
import random
import math

df = pd.read_excel('Bank Soal Petugas.xlsx', header=1)

def generate_survey(df, tipe, limit=None):
    elements = []
    
    filtered_df = df[df['Pilih soal'] == tipe].copy()
    
    if limit is not None:
        # Standardize difficulty string
        filtered_df['diff'] = filtered_df['Tingkat Kesulitan\n(Mudah/Sedang/Sulit)'].astype(str).str.strip().str.lower()
        
        mudah = filtered_df[filtered_df['diff'] == 'mudah']
        sedang = filtered_df[filtered_df['diff'] == 'sedang']
        sulit = filtered_df[filtered_df['diff'] == 'sulit']
        
        # sample 5 from each if limit is 15
        if len(mudah) >= 5 and len(sedang) >= 5 and len(sulit) >= 5 and limit == 15:
            sampled_df = pd.concat([
                mudah.sample(n=5, random_state=42),
                sedang.sample(n=5, random_state=42),
                sulit.sample(n=5, random_state=42)
            ])
            filtered_df = sampled_df.sample(frac=1, random_state=42) # shuffle overall
        else:
            # fallback: just sample limit
            filtered_df = filtered_df.sample(n=limit, random_state=42)
            
    for idx, row in filtered_df.iterrows():
        if pd.isna(row['Pertanyaan']):
            continue
            
        pertanyaan = str(row['Pertanyaan']).strip()
        jawaban_benar = str(row['Jawaban benar']).strip()
        salah1 = str(row['Jawaban Salah 1']).strip()
        salah2 = str(row['Jawaban Salah 2']).strip()
        salah3 = str(row['Jawaban Salah 3']).strip()
        
        options = [jawaban_benar, salah1, salah2, salah3]
        # Remove any nan or empty strings if any
        options = [o for o in options if o != 'nan' and o != '']
        
        # shuffle the options deterministically for reproducibility (or randomly)
        random.seed(idx) # just to make it somewhat reproducible
        random.shuffle(options)
        
        choices = []
        correct_val = ''
        letters = ['a', 'b', 'c', 'd', 'e']
        for i, opt in enumerate(options):
            val = letters[i]
            choices.append({
                "text": opt,
                "value": val
            })
            if opt == jawaban_benar:
                correct_val = val
                
        no_soal = str(row['NoSoal'])
        if no_soal == 'nan':
            no_soal = str(row['No'])
            
        # parse it as float then int if possible
        try:
            no_soal = str(int(float(no_soal)))
        except:
            pass
            
        elements.append({
            "name": f"soal_{no_soal}",
            "type": "radiogroup",
            "title": pertanyaan,
            "choices": choices,
            "isRequired": True,
            "correctAnswer": correct_val
        })
        
    survey = {
      "title": f"Tes Seleksi Mitra Statistik BPS - {tipe.capitalize()}",
      "description": "Kuesioner tes kemampuan dasar untuk seleksi mitra lapangan BPS.",
      "logoPosition": "right",
      "completedHtml": "<h3>Terima kasih telah menyelesaikan tes ini!</h3><p>Jawaban Anda telah berhasil disimpan.</p>",
      "progressBarType": "questions",
      "showProgressBar": "top",
      "goNextPageAutomatic": False,
      "questionsOnPageMode": "singlePage",
      "showQuestionNumbers": "on",
      "pages": [
        {
          "name": "halaman_identitas",
          "title": "Identitas Peserta",
          "elements": [
            {
              "name": "nama_lengkap",
              "type": "text",
              "title": "Nama Lengkap",
              "readOnly": True,
              "isRequired": True
            },
            {
              "name": "email_peserta",
              "type": "text",
              "title": "Email",
              "readOnly": True,
              "inputType": "email",
              "isRequired": True,
              "validators": [
                {
                  "type": "email"
                }
              ]
            }
          ]
        },
        {
          "name": "halaman_kuis",
          "title": f"Bagian 1: Kuis {tipe.capitalize()}",
          "elements": elements
        }
      ]
    }
    
    with open(f"{tipe}.json", "w", encoding="utf-8") as f:
        json.dump(survey, f, indent=2, ensure_ascii=False)

generate_survey(df, 'prepost', limit=15)
generate_survey(df, 'pendalaman')
