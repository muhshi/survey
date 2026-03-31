# PRD: Sistem Manajemen Survei Dinamis

## 1. Executive Summary

### Problem Statement
Organisasi membutuhkan platform survei fleksibel yang mampu menangani berbagai jenis kegiatan pengumpulan data — mulai dari pendaftaran petugas, survei lapangan (pendataan), hingga survei internal — tanpa harus membangun formulir baru secara *hardcode* untuk setiap kebutuhan.

### Proposed Solution
Membangun **Dynamic Survey Platform** berbasis **Laravel Filament** (panel admin) dan **SurveyJS** (form builder & runner). Platform ini memungkinkan admin merancang kuesioner secara visual menggunakan *drag-and-drop* SurveyJS Creator, mengkategorikan survei secara dinamis, dan mengatur mode pengisian sesuai kebutuhan lapangan.

### Success Criteria
- Admin dapat merancang kuesioner kompleks (multi-halaman, logika kondisional, validasi) 100% melalui UI visual tanpa menyentuh kode.
- Kuesioner dikelompokkan ke dalam kategori dinamis yang bisa ditambah kapan saja.
- Sistem mendukung tiga mode pengisian: sekali isi, dapat diedit, dan multi-submission (pendataan lapangan).
- Dasbor ringkasan default tersedia per survei (total responden, distribusi jawaban).

---

## 2. User Personas

### Admin / Surveyor
Pengguna internal yang mengelola seluruh siklus hidup survei:
- Membuat dan mengelola **kategori** survei.
- Mendesain kuesioner menggunakan **SurveyJS Creator** (drag-and-drop builder dengan validasi, logika skip, multi-page).
- Mengatur **mode pengisian** dan **periode aktif** survei.
- Melihat daftar respons dan ringkasan analitik dasar.

### Petugas Lapangan (Enumerator)
Pengguna yang melakukan pendataan di lapangan:
- Login ke sistem, memilih survei pendataan yang ditugaskan.
- Mengisi kuesioner **berulang kali** untuk setiap responden yang ditemui di lapangan (mode multi-submission).
- Setiap pengisian menghasilkan satu *record* terpisah.

### Responden Internal
Pengguna internal yang mengisi survei untuk keperluan tertentu:
- Mengisi formulir pendaftaran, survei kepuasan, atau kuesioner internal lain.
- Mengisi **sekali** dan tidak bisa mengubah (mode single-submit), atau bisa mengedit jawaban sebelum batas waktu (mode editable).

---

## 3. Konsep Inti

### 3.1 Kategori Survei
Setiap survei harus masuk ke dalam satu kategori. Kategori bersifat **dinamis** — admin bisa menambah kategori baru kapan saja tanpa intervensi developer.

**Contoh kategori:**

| Kategori | Deskripsi | Mode Tipikal |
|---|---|---|
| Pendaftaran Petugas | Formulir rekrutmen petugas lapangan | Single-submit |
| Survei Lapangan | Pendataan responden oleh petugas | Multi-submit |
| Survei Internal | Kepuasan kerja, evaluasi, feedback | Single / Editable |
| Registrasi Kegiatan | Formulir pendaftaran acara / pelatihan | Single-submit |

### 3.2 Mode Pengisian Kuesioner
Setiap survei memiliki konfigurasi **mode pengisian** yang menentukan perilaku form bagi responden:

| Mode | Perilaku | Use Case |
|---|---|---|
| `single` | Responden hanya bisa mengisi **satu kali**, tidak bisa diedit setelah submit. | Pendaftaran, registrasi |
| `editable` | Responden mengisi satu kali, tetapi bisa **mengedit** jawabannya selama survei masih aktif. | Survei internal yang perlu revisi |
| `multi` | Responden bisa mengisi kuesioner yang sama **berkali-kali**, setiap submit menghasilkan record baru. | Pendataan lapangan (1 petugas → banyak responden) |

### 3.3 SurveyJS Integration
Platform ini sepenuhnya mengandalkan ekosistem **SurveyJS** untuk dua hal:

- **SurveyJS Creator** (Admin) — Form builder visual di panel admin. Admin menyusun pertanyaan, validasi, logika skip, dan tata letak halaman. Output-nya adalah **JSON Schema** yang disimpan di database.
- **SurveyJS Runner** (Responden) — Engine rendering form dari JSON Schema. Responden melihat kuesioner interaktif dengan validasi *real-time*. Output-nya adalah **JSON Payload** jawaban yang dikirim ke server.

> **Non-Goal**: Kita tidak membangun form builder sendiri. Seluruh mekanisme desain form, validasi, dan logika kondisional ditangani oleh SurveyJS.

---

## 4. User Stories

### Admin / Surveyor
- *As an Admin*, saya ingin **membuat kategori baru** kapan saja sehingga survei-survei bisa dikelompokkan sesuai konteks kegiatan.
- *As an Admin*, saya ingin **membangun kuesioner secara visual** (drag-and-drop) menggunakan SurveyJS Creator sehingga tidak perlu bantuan programmer.
- *As an Admin*, saya ingin **mengatur mode pengisian** (single / editable / multi) pada setiap survei sehingga perilaku form sesuai kebutuhan kegiatan.
- *As an Admin*, saya ingin **melihat daftar respons** dan statistik ringkasan (jumlah responden, distribusi jawaban dasar) per survei.
- *As an Admin*, saya ingin **mengaktifkan atau menonaktifkan** survei kapan saja.

### Petugas Lapangan
- *As a Petugas*, saya ingin **mengisi kuesioner berulang** untuk setiap warga/responden yang saya datangi, dan setiap pengisian tersimpan sebagai record terpisah.
- *As a Petugas*, saya ingin **melihat riwayat entry** yang sudah saya kirimkan pada survei tertentu.

### Responden Internal
- *As a Responden*, saya ingin **mengisi form dengan validasi real-time** sehingga saya tahu kesalahan input sebelum submit.
- *As a Responden*, saya ingin **mengedit jawaban** saya jika survei mengizinkan mode edit.

---

## 5. Technical Specifications

### 5.1 Technology Stack
| Layer | Teknologi |
|---|---|
| Backend | Laravel 13.x, PHP 8.4 |
| Admin Panel | Filament v5 (Livewire 4) |
| Form Builder | SurveyJS Creator (embed via CDN/NPM + Alpine.js wrapper) |
| Form Runner | SurveyJS Library (embed di Blade view) |
| Styling | Tailwind CSS v4 |
| Database | MySQL / SQLite (JSON column support) |
| Testing | Pest v4 |

### 5.2 Database Schema

```
┌──────────────────┐       ┌───────────────────┐       ┌────────────────────────┐
│ survey_categories │       │     surveys        │       │   survey_submissions   │
├──────────────────┤       ├───────────────────┤       ├────────────────────────┤
│ id               │──┐    │ id                │──┐    │ id                     │
│ name             │  │    │ survey_category_id │◄─┘    │ survey_id              │◄─┘
│ slug             │  │    │ title             │       │ user_id (nullable)     │
│ description      │  └──►│ slug              │       │ payload (JSON)         │
│ created_at       │       │ schema (JSON)     │       │ metadata (JSON)        │
│ updated_at       │       │ mode (enum)       │       │ submitted_at           │
└──────────────────┘       │ is_active         │       │ created_at             │
                           │ starts_at         │       │ updated_at             │
                           │ ends_at           │       └────────────────────────┘
                           │ created_at        │
                           │ updated_at        │
                           └───────────────────┘
```

**Keterangan kolom penting:**
- `surveys.schema` — JSON Schema dari SurveyJS Creator (definisi pertanyaan, validasi, logika).
- `surveys.mode` — Enum: `single`, `editable`, `multi`.
- `surveys.is_active` — Boolean, menandai apakah survei bisa diisi.
- `surveys.starts_at` / `ends_at` — Periode aktif survei (nullable, opsional).
- `survey_submissions.payload` — JSON jawaban dari SurveyJS Runner.
- `survey_submissions.metadata` — Data kontekstual (IP, browser, timestamp detail).

### 5.3 Architecture Overview

```
┌─────────────────────────────────────────────────┐
│                  Admin Panel                     │
│              (Filament v5)                       │
│                                                  │
│  ┌──────────┐  ┌───────────┐  ┌──────────────┐  │
│  │ Category │  │  Survey   │  │ Submissions  │  │
│  │   CRUD   │  │   CRUD    │  │   Viewer     │  │
│  └──────────┘  └─────┬─────┘  └──────────────┘  │
│                      │                           │
│            ┌─────────▼──────────┐                │
│            │  SurveyJS Creator  │                │
│            │  (Alpine.js wrap)  │                │
│            │  wire:ignore       │                │
│            └─────────┬──────────┘                │
│                      │ JSON Schema               │
└──────────────────────┼───────────────────────────┘
                       │
                       ▼
              ┌────────────────┐
              │   Database     │
              │  surveys.schema│
              └───────┬────────┘
                      │
        ┌─────────────▼──────────────┐
        │     Respondent Frontend    │
        │     (Blade + Livewire)     │
        │                            │
        │  ┌──────────────────────┐  │
        │  │   SurveyJS Runner    │  │
        │  │   (renders from      │  │
        │  │    JSON Schema)      │  │
        │  └──────────┬───────────┘  │
        │             │ JSON Payload │
        └─────────────┼──────────────┘
                      │
                      ▼
           ┌──────────────────┐
           │survey_submissions│
           │  .payload (JSON) │
           └──────────────────┘
```

### 5.4 Mode Enforcement Logic

```
Saat responden submit:

IF mode = 'single'
  → Cek apakah user sudah punya submission untuk survey ini
  → Jika sudah: tolak (403)
  → Jika belum: simpan, set read-only

IF mode = 'editable'
  → Cek apakah user sudah punya submission
  → Jika sudah: update submission yang ada
  → Jika belum: buat submission baru

IF mode = 'multi'
  → Selalu buat submission baru (tanpa pengecekan duplikasi)
  → Catat user_id sebagai enumerator/petugas
```

### 5.5 SurveyJS Integration Strategy
- **Creator di Admin**: Di-embed dalam custom Filament form field menggunakan `wire:ignore` + Alpine.js untuk menghindari konflik dengan Livewire DOM diffing. JSON schema di-sync ke Livewire property via Alpine `$wire`.
- **Runner di Frontend**: Blade view standar yang me-load SurveyJS library dari CDN, menginisialisasi model dari JSON schema, dan mengirim hasil via AJAX/Livewire action ke backend.
- **Lisensi**: Penggunaan internal & non-komersial — aman di bawah lisensi SurveyJS tanpa langganan komersial.

### 5.6 Security & Privacy
- Validasi payload di backend: memastikan JSON jawaban sesuai dengan schema asli survei.
- Proteksi mode enforcement di server-side (bukan hanya di frontend).
- CSRF protection pada semua form submission.
- Rate limiting pada endpoint submission untuk mencegah abuse.

---

## 6. Default Dashboard & Analytics

Setiap survei otomatis mendapat **dasbor ringkasan default** di panel admin:
- **Total Responden** — Jumlah submission yang masuk.
- **Submission Timeline** — Grafik jumlah submission per hari/minggu.
- **Distribusi Jawaban** — Untuk pertanyaan tipe pilihan (radio, dropdown, checkbox): pie/bar chart dari distribusi jawaban.

> Analitik lanjutan (scoring, indeks, formula khusus) bersifat **per-kuesioner** dan akan ditambahkan secara manual/inkremental sesuai kebutuhan masing-masing survei.

---

## 7. Roadmap

### Phase 1 — MVP Foundation
- Setup database schema (categories, surveys, submissions).
- CRUD Kategori di Filament.
- CRUD Survei di Filament dengan **SurveyJS Creator** embedded.
- Halaman pengisian survei (SurveyJS Runner) via Blade + Livewire.
- Mode enforcement (single / editable / multi).
- Penyimpanan raw JSON submission.

### Phase 2 — Analytics & Polish
- Dasbor ringkasan default per survei (total responden, distribusi jawaban).
- Riwayat submission per petugas (untuk mode multi).
- Export data submission ke CSV/Excel.
- Notifikasi (opsional).

### Phase 3 — Advanced Features
- Custom dashboard per kategori/survei tertentu.
- Optimasi query untuk volume data besar (JSON → tabel turunan).
- Role-based access: pembatasan siapa yang bisa mengisi survei tertentu.
- Offline-capable form untuk pendataan lapangan (Progressive Web App).

---

## 8. Technical Risks & Mitigations

| Risk | Impact | Mitigation |
|---|---|---|
| **Livewire ↔ SurveyJS DOM conflict** | Creator/Runner tidak render dengan benar | Gunakan `wire:ignore` + Alpine.js wrapper untuk isolasi DOM SurveyJS dari Livewire |
| **JSON query performance** | Lambat saat analitik pada volume besar | Event/Job Laravel untuk ekstrak data JSON ke tabel relasional saat submit |
| **Schema versioning** | Perubahan schema setelah ada submission bisa merusak analitik | Simpan schema snapshot per submission, atau lock schema setelah ada data masuk |
| **Mode enforcement bypass** | User memanipulasi request untuk submit berkali-kali pada mode single | Enforcement wajib di server-side, bukan hanya client |
