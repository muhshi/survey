---
name: survey-quiz-builder
description: "Gunakan skill ini setiap kali membuat, merancang, mengimpor, atau memperbarui kuesioner dan kuis (pre-test, post-test, pendalaman, evaluasi) di platform survei ini. Menjelaskan konvensi skema SurveyJS, integrasi identitas otomatis pengguna login, pengaturan kuis (passing score, retake), pembobotan nilai, dan integrasi dengan Kelompok Survei (Group)."
license: MIT
metadata:
  author: bps-demak
---

# Panduan Pembuatan Survei & Kuis (Survey Quiz Builder)

Panduan standar pembuatan kuis evaluasi, pre-test, post-test, dan pendalaman di sistem survei BPS Demak berbasis Laravel Filament dan SurveyJS.

---

## 1. Aturan Sumber Soal (PENTING: Jangan Mengarang/Ngawang-Ngawang)

1. **Gunakan Bank Soal / Materi Resmi Pengguna**:
   - Jika pengguna menyebutkan kegiatan tertentu (misal Pelatihan Wilkerstat SE2026, Susenas, Sakernas, dll.), **selalu minta atau periksa bank soal resmi** dari pengguna (tabel spreadsheet, foto, atau dokumen Word/PDF).
   - Jangan membuat/mengarang butir soal fiktif jika materi ujian sudah memiliki kisi-kisi atau tabel soal baku dari panitia pelatihan.

---

## 2. Struktur Skema SurveyJS Kuis

File JSON skema kuis disimpan di root direktori dengan penamaan `kebab-case.json` (contoh: `pretest-pengolahan-wilkerstat-se2026.json`).

### A. Halaman 1: Identitas Peserta (`page_identitas`)
- **DILARANG** membuat dropdown manual daftar nama peserta di dalam form soal.
- Sistem runner (`resources/views/survey/show.blade.php`) sudah memiliki mekanisme **auto-prefill** dari sesi login pengguna aktif (`Auth::user()`).
- Gunakan field `nama_lengkap` dan `email_peserta` dengan atribut `"readOnly": true` agar terkunci otomatis:

```json
{
  "name": "page_identitas",
  "title": "Identitas Peserta",
  "description": "Identitas peserta otomatis dimuat dari akun login sistem.",
  "elements": [
    {
      "type": "text",
      "name": "nama_lengkap",
      "title": "Nama Lengkap",
      "readOnly": true,
      "isRequired": true
    },
    {
      "type": "text",
      "name": "email_peserta",
      "title": "Email Peserta",
      "readOnly": true,
      "inputType": "email",
      "isRequired": true
    }
  ]
}
```

### B. Halaman 2: Butir Soal Kuis (`page_soal`)
- Pertanyaan pilihan ganda menggunakan `"type": "radiogroup"`.
- Format nama soal terurut: `soal_1`, `soal_2`, `soal_3`, dst.
- Opsi jawaban menggunakan huruf kecil: `"value": "a"`, `"b"`, `"c"`, `"d"`.
- Kunci jawaban dicantumkan di properti `"correctAnswer": "a"`.
- *Catatan Acak Pilihan*: Karena sistem frontend secara otomatis mengacak urutan pilihan jawaban (`choicesOrder = "random"`), kunci jawaban di file JSON bisa diletakkan konsisten pada value `"a"` (Jawaban Benar) dan `"b"`, `"c"`, `"d"` (Pengecoh). Peserta akan melihat opsi teracak saat pengerjaan.
- Bobot nilai (`score`): Berikan bobot yang proporsional sehingga total skor = 100 (misal: 20 soal = masing-masing 5 poin; 5 soal = masing-masing 20 poin).

Contoh elemen soal:
```json
{
  "name": "soal_1",
  "type": "radiogroup",
  "title": "Teks pertanyaan soal...",
  "choices": [
    { "value": "a", "text": "Teks jawaban yang benar" },
    { "value": "b", "text": "Pengecoh 1" },
    { "value": "c", "text": "Pengecoh 2" },
    { "value": "d", "text": "Pengecoh 3" }
  ],
  "isRequired": true,
  "score": 5,
  "correctAnswer": "a"
}
```

---

## 3. Konfigurasi Model Survey & Settings

Setiap kuis disimpan di tabel `survey` dengan spesifikasi berikut:

- `mode`: `\App\Enums\SurveyMode::Single` (kuis selalu mode sekali isi/submit per sesi)
- `is_quiz`: `true`
- `access_level`: `'public'` (karena jika ditautkan ke Kelompok, sistem otomatis mewajibkan login)
- `settings`:
  - `passing_score`: `70` (nilai standar kelulusan)
  - `allow_retake`: `false` untuk Pre-Test, `true` untuk Post-Test dan Pendalaman
  - `max_retakes`: `1` untuk Pre-Test, `2` s.d. `3` untuk Post-Test/Pendalaman

---

## 4. Integrasi Kelompok Survei (Group)

1. **Jangan Input Manual di Form**: Kelola pembagian peserta melalui menu **Kelompok Survei** (`groups`).
2. **Pencarian User Fleksibel**:
   - Cari akun pengguna di tabel `users` berdasarkan kecocokan nama (`like %nama%`).
   - Perhatikan variasi ejaan nama di database (misal huruf kapital, singkatan, gelar, atau spasi).
3. **Penautan Many-to-Many**:
   - Tautkan user ke kelompok: `$group->users()->syncWithoutDetaching($userIds);`
   - Tautkan kuis ke kelompok: `$survey->groups()->syncWithoutDetaching([$group->id]);`
4. **Otomatisasi via Artisan**:
   - Selalu sediakan perintah Artisan (seperti `survey:assign-wilkerstat-group`) atau seeder (`WilkerstatQuizSeeder`) agar proses pembuatan survei dan penugasan kelompok dapat dieksekusi secara instan dan dapat diulang (*idempotent*).
