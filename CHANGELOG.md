# Changelog

Semua perubahan penting dalam proyek ini akan dicatat di file ini.
Format ini didasarkan pada [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

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
