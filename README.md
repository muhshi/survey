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

### 2026-06-10
- **Quiz Recap in Admin**: Menambahkan fitur halaman Rekap Kuis di panel admin (terkait langsung ke survei yang bertipe kuis) untuk melihat daftar peserta beserta skor kuis terbaik mereka, total percobaan, dan status kelulusan (Lulus/Belum Lulus) berdasarkan nilai minimum (`passing_score`). Dilengkapi juga dengan filter status kelulusan.
- **Fix MySurveyPage Enum Error**: Memperbaiki error `Call to undefined method App\Enums\SurveyMode::getLabel()` pada `my-survey-page.blade.php` dengan memanggil nama fungsi yang benar, yakni `label()`.

### 2026-06-09
- **Alokasi Petugas Form**: Menambahkan skema formulir "Alokasi Petugas SE2026" berformat SurveyJS.
- **Hierarchical Region API**: Menambahkan dukungan API hierarkis bertingkat (Kecamatan -> Desa/Kelurahan -> SLS -> Sub SLS) di `RegionController` untuk memfasilitasi filter *dependent dropdown* (pilihan wilayah berjenjang) pada form SurveyJS.

### 2026-06-06
- **Multi-Survey Filter & Export**: Menambahkan kemampuan filter `survey_id` dengan mode `multiple` di halaman Jawaban Responden, serta memungkinkan fitur Export Excel untuk mengunduh gabungan data (termasuk kolom dinamis) dari beberapa survei sekaligus secara otomatis.

### 2026-06-03
- **Fix JSON Generation Duplicate Names**: Memperbaiki bug pada file kuesioner `pendalaman.json` yang memiliki duplikasi atribut `name` (misal `soal_4` muncul berulang kali) akibat manipulasi manual sebelumnya, yang menyebabkan jawaban saling tertimpa saat dipilih. Script `generate_survey.py` telah disesuaikan untuk mempertahankan field identitas khusus (gelombang, tc_hotel, kelas) dan dijalankan ulang untuk mengenerate ID soal yang unik (sequensial) di seluruh kuesioner.


### 2026-06-01
- **Survey Cohort Completion Monitoring**: Menambahkan kolom *Belum Mengerjakan* pada tabel Kelompok Survei di halaman detail Survei untuk memantau jumlah peserta di masing-masing kelompok yang belum menyelesaikan survei/kuis tersebut secara real-time.
- **Multi-Select & Exclude Existing Members in Group**: Memperbarui aksi `AttachAction` pada `UsersRelationManager` kelompok survei agar mendukung pemilihan banyak user sekaligus (multi-select) serta menyaring daftar pilihan untuk mengecualikan user yang sudah terdaftar di kelompok tersebut.
- **Pretest & Pendalaman Monitoring Columns**: Menambahkan kolom status pengerjaan pretest (ID 4) dan pendalaman (ID 5) beserta skor peserta secara langsung di tabel anggota kelompok survei dengan dukungan eager loading agar terhindar dari N+1 query.
- **Pretest & Pendalaman Status Filters**: Menambahkan filter seleksi status pengerjaan pretest dan pendalaman untuk mempermudah admin memilah siapa saja peserta kelompok yang belum atau sudah menyelesaikan ujian.
- **Fix Group Member Addition Error**: Memperbaiki `BadMethodCallException` dengan mendefinisikan relasi `groups()` pada model `User` sebagai relasi `BelongsToMany` dengan model `Group`. Ini memecahkan kegagalan penautan anggota baru kelompok survei di admin panel Filament.
- **Fix Kuis Pretest Soal Sudah Terjawab**: Memperbaiki bug di mana peserta yang membuka kuis pretest mendapati sebagian soal sudah terjawab. Penyebabnya adalah `$existingSubmission` (jawaban lama) tidak di-null-kan untuk mode `single` maupun kuis (`is_quiz`), sehingga SurveyJS memuat ulang payload jawaban sebelumnya ke dalam form baru.

### 2026-05-30
- **Group-Survey Many-to-Many Relationship**: Migrasi relasi antara Kelompok Survei (Group) dan Survei dari One-to-Many menjadi Many-to-Many dengan tabel pivot `group_survey`. Memungkinkan satu kelompok (misal Gelombang I) terdaftar di beberapa kuesioner sekaligus (Pretest dan Pendalaman).
- **Survey Access Control & Detailed Warnings**: Mengubah logika pengecekan ketersediaan survei dan otorisasi kelompok menjadi lebih deskriptif. Jika pengguna tidak memiliki akses kelompok, belum masuk ke waktu aktif, atau waktu aktif telah berakhir, sistem akan mengembalikan pesan error yang spesifik (misal: "Akses Belum Dibuka" atau "Akun Anda tidak terdaftar dalam kelompok...").
- **Group Pivot Timeframe Overrides**: Menambahkan kolom `starts_at` dan `ends_at` pada tabel pivot `group_survey` yang memungkinkan penentuan rentang waktu berlaku kelompok yang berbeda untuk masing-masing kuesioner, di mana sistem akan menggunakan waktu pivot ini dan melakukan fallback ke waktu default kelompok jika tidak didefinisikan.
- **Enhanced Filament GroupsRelationManager**: Menambahkan aksi "Buat Kelompok Baru", kemampuan mengisi dan mengubah rentang waktu khusus survei (pivot) pada aksi Attach dan Edit, serta menambahkan tombol eksternal "Buka Kelompok" untuk memudahkan pengelolaan anggota kelompok survei.
- **Quiz JSON Generation**: Membuat script pembuat kuesioner otomatis `generate_survey.py` dan men-generate `prepost.json` (15 soal campuran mudah, sedang, sulit) serta `pendalaman.json` (seluruh soal) dari Excel Bank Soal Petugas.

### 2026-05-29
- **Excel Import Template Download**: Menambahkan tombol *Unduh Template* pada header tabel relasi Groups untuk mengunduh contoh file Excel (format `.xlsx` dengan kolom header `email` dan `name`) yang kompatibel untuk proses impor user.
- **Survey Groups (Cohorts)**: Mengimplementasikan sistem Kelompok Survei/Gelombang yang memungkinkan akses survei dibatasi pada waktu tertentu untuk sekelompok pengguna spesifik tanpa perlu membuat role global baru.
- **Excel User Import to Group**: Menambahkan tombol *Import Users* pada Filament *GroupsRelationManager* untuk mengimpor dan mendaftarkan user massal dari file Excel secara langsung ke dalam suatu Kelompok Survei, dengan pembuatan akun otomatis untuk user yang belum terdaftar (password default `Mitra3321`).

### 2026-05-27
- **Fix Export Error in ListJawabanResponden**: Memperbaiki `ArgumentCountError` pada fungsi `getTableFilterState()` saat melakukan export data Jawaban Responden dengan memberikan argumen nama filter spesifik (`survey_id` & `submitted_at`) yang dibutuhkan oleh Filament v5.

### 2026-05-26
- **Import Calon Afirmasi Command**: Menambahkan perintah `survey:import-calon-afirmasi` untuk mengimpor calon petugas afirmasi langsung dari file Excel `petugas afirmasi.xlsx`, lengkap dengan pembuatan otomatis role `calon_afirmasi`, alokasi nomor urut berurutan (*auto-increment*), default password `Mitra3321`, dan parsing cerdas untuk kolom kecamatan dan desa.
- **Calon Afirmasi User Tab**: Menambahkan tab filter "Calon Afirmasi" pada halaman manajemen pengguna (ManageUsers) di Filament untuk mempermudah pemantauan dan pencarian data calon petugas afirmasi.
- **Add Calon Afirmasi to Spreadsheet & DB**: Menambahkan data calon afirmasi baru atas nama ananda Riza mahfudzi, Ghulam Za'imul Haq, Lailatus Sa'adah, Abdus salam, MOHAMAD HARIS WIYANTO, herdi sofyan, Nailul Muna, Choirul fasikhin, Mohamad Khoirul Huda, sri lestari, Suyanti, muhammad dany Ariyanto, Itsni Faiq, Dwi Selvaa Maulidenna, dan Meylla erviana putri ke dalam file Excel `petugas afirmasi.xlsx` serta mendaftarkannya secara langsung ke dalam database.
- **Date Range Filter & Excel Sync**: Menambahkan komponen filter rentang tanggal (*Date Range Filter*) untuk waktu submit jawaban responden di tabel utama Jawaban Responden maupun di halaman detail Submissions, serta mengintegrasikan filter aktif tersebut secara dinamis ke fungsi Export Excel.

### 2026-05-19
- **Searchable Respondents**: Membuat kolom Responden/Peserta pada tabel Jawaban Responden dapat dicari (*searchable*), tidak hanya berdasarkan pewawancara, tetapi juga mencari berdasarkan `nama_peserta` di dalam *payload* survei.
- **Handle Missing Users**: Menangani masalah nama peserta yang tidak muncul atau bernilai `Unknown` dengan menampilkan string nama (atau ID fallback) secara langsung jika data user tidak ditemukan di database.
- **Dashboard Widget**: Menambahkan grafik garis untuk jumlah inputan jawaban per hari pada dashboard admin Filament, lengkap dengan filter berdasarkan Survei.

### 2026-05-18
- **Survey Dropdown Sort**: Mengubah urutan dropdown nama peserta di form wawancara agar diurutkan berdasarkan `nomor_urut` mulai dari terkecil.
- **Survey Mobile Keyboard Fix**: Memperbaiki popup dropdown SurveyJS di HP yang tertutup navbar. Popup sekarang dipaksa *full-screen overlay* (`z-index: 10000`) di perangkat mobile, dengan `MutationObserver` + interval berkala untuk memastikan `readonly` dan `inputmode="none"` selalu dihapus sehingga keyboard HP pasti muncul.
- **Multi-mode Fresh Reset**: Memperbaiki isu pada survei mode *Multi* di mana _refresh_ halaman justru me-load ulang jawaban terakhir, sekarang dipastikan _form_ selalu kosong untuk pengisian baru, dan ditambahkan tombol khusus "Isi Kuesioner Baru" pada halaman penyelesaian.
- **No-Cache & View Jawaban**: Memperbaiki isu _cache_ pada API Dropdown Peserta sehingga nama yang sudah diwawancarai langsung hilang dari daftar tanpa perlu hard refresh, serta memperjelas tampilan nama Peserta vs Pewawancara di tabel dashboard Filament.

### 2026-05-16
- **Participant Search & Auto-fill**: Implementasi dropdown nama peserta yang dapat dicari (searchable) dan pengisian otomatis nomor urut (bidirectional auto-fill) pada form wawancara.
- **Excel Participant Import**: Menambahkan perintah `app:import-participants` untuk mengimpor data peserta wawancara dari file Excel melalui JSON intermediate.
- **Uji Kompetensi Auto-fill**: Automasi pengisian Nama dan Email dari session login user.
- **Uji Kompetensi Schema**: Menghapus field Nomor Telepon (WA) dari kuesioner Uji Kompetensi.
- **Quiz Randomization**: Implementasi pengacakan urutan soal dan pilihan jawaban secara otomatis untuk semua survei bertipe kuis.

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
