# Sistem Manajemen Survei Dinamis (SurveyJS + Filament)

Platform survei dinamis yang memungkinkan pembuatan kuesioner kompleks menggunakan SurveyJS dan dikelola melalui Laravel Filament.

## Fitur Utama

- **SurveyJS Creator**: Form builder visual (drag-and-drop) di panel admin.
- **Premium UI/UX**: Desain antarmuka elegan berbasis Navy & Sky Blue dengan tipografi modern (Outfit).
- **Responsive Submissions**: Tampilan jawaban responden berbasis kartu yang bersih dan terstruktur.
- **Dynamic Categories**: Pengelompokan survei ke dalam kategori yang dinamis.
- **Flexible Modes**: 
  - `single`: Satu kali isi per user.
  - `editable`: Bisa diedit setelah submit.
  - `multi`: Pengisian berkali-kali (untuk petugas lapangan).
- **Insightful Analytics**: Statistik ringkasan responden dan distribusi jawaban secara real-time.

## Tech Stack

- **Framework**: Laravel 13.x (PHP 8.4)
- **Admin Panel**: [Filament v5](https://filamentphp.com/)
- **Authorization**: [Filament Shield](https://github.com/bezhanSalleh/filament-shield)
- **Form Engine**: [SurveyJS](https://surveyjs.io/)
- **Database**: SQLite
- **Styling**: Tailwind CSS v4 & Heroicons

## Catatan Perubahan

Riwayat pembaruan dan pengembangan proyek dapat dilihat di [CHANGELOG.md](CHANGELOG.md).

## Instalasi

Pastikan Anda memiliki PHP 8.4+ dan Composer terinstal.

1. **Clone repositori**:
   ```bash
   git clone https://github.com/muhshi/survey.git
   cd survey
   ```

2. **Instal dependensi PHP**:
   ```bash
   composer install
   ```

3. **Instal dependensi Frontend**:
   ```bash
   npm install && npm run build
   ```

4. **Setup Environment**:
   Salin `.env.example` ke `.env` dan pastikan `DB_CONNECTION` diset ke `sqlite`.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database Setup**:
   ```bash
   # Jalankan migrasi
   php artisan migrate --seed
   ```

6. **Filament Shield Setup**:
   Pastikan permissions dan user admin sudah digenerate:
   ```bash
   php artisan shield:install
   php artisan shield:super-admin


7. **Jalankan Aplikasi**:
   ```bash
   php artisan serve
   ```

## Penggunaan

- Akses Panel Admin di `/admin`.
- Buat Kategori Survei terlebih dahulu.
- Buat Survei baru dan gunakan Builder SurveyJS untuk merancang kuesioner.
- Bagikan link survei ke responden / petugas lapangan sesuai mode yang dipilih.

---
*Proyek ini dikembangkan menggunakan Laravel Boost.*
