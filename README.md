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

## Changelog

### 2026-05-13
- **Fix Redirect After Login**: Memperbaiki alur redirect sehingga user diarahkan kembali ke link survei asal setelah login (intended URL), alih-alih selalu masuk ke dashboard admin.

### 2026-05-12
- **Quiz Mode (Uji Kompetensi)**: Menambahkan dukungan penilaian otomatis (scoring) berdasarkan `correctAnswer` pada schema SurveyJS.
- **Score Tracking**: Menambahkan kolom skor pada tabel jawaban responden dan tampilan dashboard admin.
- **Mass User Import**: Membuat Artisan command `survey:import-pendaftar` untuk mengimpor data pendaftar massal (Email sebagai username, Tgl Lahir sebagai password).
- **Hide Slugs from UI**: Menghapus input slug pada form Kategori dan Survey untuk menyederhanakan antarmuka.
- **Automatic Slug Generation**: Implementasi pembuatan slug unik otomatis oleh sistem pada model Kategori dan Survey.
- **Improve Survey Link Sharing**: Menambahkan kolom "Link Survei" yang dapat disalin langsung pada tabel survey dan menghapus tombol salin link yang tidak berfungsi.

### 2026-05-08
- **Fix SSO Integration**: Resolving `BindingResolutionException` by installing missing Socialite dependencies.
- **Fix SSO Login Flow**: Making `password` column nullable on `users` table and fixing login route name in `SsoController`.
- **Fix User Roles**: Ensuring `pegawai` role exists during SSO user registration.
- **Improve SSO UI**: Updating BPS logo to high-quality local asset to prevent broken images.
- **Clean SurveyJS UI**: Hiding SurveyJS license banners and watermarks in both builder and runner for a cleaner look.

### 2026-05-06
- **Implement SIPETRA SSO**: Integrasi autentikasi OAuth2 menggunakan Laravel Socialite untuk login terpusat.
- **Implement Master Data Sync**: Penambahan perintah `sync:users` untuk sinkronisasi data pegawai dan mitra dari API Master Sipetra.
- **Database Update**: Penambahan kolom data profil lengkap pada tabel `users`.
- **UI Update**: Integrasi tombol "Masuk dengan SIPETRA SSO" di halaman login Filament.
- **Fix Dev Environment**: Memperbaiki perintah `composer dev` agar kompatibel dengan Windows (menghapus `php artisan pail`).
- **Fix Vite**: Menginstal dependensi Node.js (`npm install`).
- **Docker Setup**: Penambahan konfigurasi Docker (Dockerfile, docker-compose.yml, Caddyfile) berbasis FrankenPHP 8.4. Dockerfile kini menyertakan Node.js, `composer install`, dan `npm run build` otomatis untuk kemudahan deployment.
- **Fix SSO UI**: Membersihkan file `sso-button.blade.php` dari sisa simbol diff (`+`) yang tertempel tidak sengaja.

Riwayat lengkap dapat dilihat di [CHANGELOG.md](CHANGELOG.md).

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
