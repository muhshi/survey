# Changelog

Semua perubahan penting dalam proyek ini akan dicatat di file ini.
Format ini didasarkan pada [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [Unreleased]

### Changed
- **Improve User Import**: Memperbarui fitur "Import Calon Petugas" di halaman ManageUsers Filament menjadi "Import / Tambah User". Menambahkan dukungan import menggunakan file Excel, input manual secara *multiple* (banyak sekaligus), serta kemampuan menentukan Role untuk semua user yang diimpor secara otomatis dengan password default `Mitra3321`.

## [1.4.0] - 2026-06-05

### Added
- **My Survey Page**: Halaman dashboard peserta di panel admin (`/admin/my-survey-page`) yang menampilkan semua survey yang pernah diisi oleh user login, lengkap dengan skor terbaik, status lulus/belum lulus, progress bar menuju kelulusan, riwayat semua percobaan (collapsible), dan tombol aksi (Ulangi Kuis / Edit Jawaban / Lihat Survey).
- **Notifikasi Hasil Kuis**: Setelah submit kuis, sistem menampilkan modal interaktif berisi skor akhir, status lulus/tidak lulus, nilai standar kelulusan, dan tombol "Ulangi Kuis" jika masih diizinkan mengulang — tanpa perlu reload halaman.
- **Multi-Survey Filter & Export**: Menambahkan kemampuan filter `survey_id` dengan mode `multiple` di halaman Jawaban Responden, serta memungkinkan fitur Export Excel untuk mengunduh gabungan data (termasuk kolom dinamis) dari beberapa survei sekaligus secara otomatis.
- **Tombol Refresh Tabel Jawaban Responden**: Menambahkan tombol refresh di header tabel Jawaban Responden pada panel admin, untuk me-refresh data tabel secara Livewire tanpa reload halaman penuh.

### Changed
- **SurveyController `submit()`**: Response JSON submit kuis kini mengembalikan data tambahan (`quiz.score`, `quiz.passing_score`, `quiz.passed`, `quiz.can_retake`) untuk keperluan notifikasi frontend.

## [1.3.0] - 2026-05-16


### Added
- **Participant Search & Auto-fill**: Dropdown nama peserta searchable dan bidirectional auto-fill nomor urut pada form wawancara.
- **Excel Participant Import**: Artisan command `app:import-participants` untuk impor data dari Excel.

### Changed
- **Uji Kompetensi Auto-fill**: Automasi pengisian Nama dan Email dari session login user.
- **Uji Kompetensi Schema**: Menghapus field Nomor Telepon (WA) dari kuesioner Uji Kompetensi.
- **Quiz Randomization**: Implementasi pengacakan urutan soal dan pilihan jawaban secara otomatis untuk semua survei bertipe kuis.

## [1.2.0] - 2026-05-13

### Fixed
- **Login Redirection**: Menggunakan `redirect()->guest()` pada controller survei agar URL asal disimpan dan dikembalikan setelah login berhasil (intended URL).

## [1.1.1] - 2026-05-06

### Added
- **SIPETRA SSO**: Implementasi Single Sign-On menggunakan OAuth2 (Laravel Socialite).
- **Master Data Sync**: Fitur sinkronisasi otomatis data pegawai dan mitra melalui `php artisan sync:users`.
- **User Scopes**: Penambahan scope `active()`, `pegawai()`, dan `mitra()` pada model User untuk mempermudah pemfilteran data.

### Fixed
- **Development Environment**: Menghapus `php artisan pail` dari script `composer dev` karena tidak kompatibel dengan Windows (ekstensi `pcntl` hilang).
- **Dependencies**: Melakukan `npm install` untuk memastikan Vite tersedia.

---

## [1.1.0] - 2026-04-16

### Added
- Integrasi font **Outfit** untuk tampilan tipografi yang lebih modern dan premium.
- Dokumentasi `CHANGELOG.md` untuk pelacakan versi proyek.

### Changed
- **Rebranding Visual**: Mengubah tema warna utama dari Amber menjadi **Sky Blue & Navy** agar lebih profesional.
- **Redesain Halaman Jawaban (v2)**: Implementasi **Tabel Premium Modern** dengan baris detail yang dapat diekspansi (expandable), menggantikan layout kartu sebelumnya untuk efisiensi ruang yang lebih baik.
- **Peningkatan Detail Jawaban**: Row expansion terintegrasi dengan grid data yang bersih dan border aksen Sky Blue.
- **Dashboard Polish**: Memperbarui widget statistik dengan efek hover interaktif dan skema warna yang konsisten.

### Removed
- Widget "Welcome" (AccountWidget) dari dashboard untuk tampilan yang lebih bersih.
- Widget info "Filament" bawaan.
- Branding teks "Filament" pada navigasi, diganti dengan nama aplikasi "Survey BPS".

---

## [1.0.0] - 2026-04-10

### Added
- Inisialisasi proyek Sistem Manajemen Survei Dinamis.
- Integrasi **SurveyJS Creator** untuk membangun kuesioner secara visual.
- Implementasi sistem kategori survei.
- Fitur mode survei (`single`, `editable`, `multi`).
- Sistem otorisasi menggunakan Filament Shield (Role-based access).
- Dashboard statistik awal untuk ringkasan survei.
