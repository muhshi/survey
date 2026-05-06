# CHANGELOG — SIPETRA SSO Server

Dokumen ini mencatat seluruh perubahan signifikan pada proyek SIPETRA SSO Server,
dibandingkan dari versi **awal** ke versi **terbaru** (`sipetra`).

> Format mengikuti [Keep a Changelog](https://keepachangelog.com/id-ID/1.1.0/).

---

## [2026-05-04] — Documentation & Background Sync Refinement

### ✅ Changed
- **`GEMINI.md`** — Menambahkan instruksi pola Background Job untuk sinkronisasi client-side agar mencegah *browser timeout*.
- **`docs/API_MASTER_USERS.md`** — Memperbarui referensi tombol sync manual di Filament agar menggunakan pola `SyncUsersJob` (Background Job) sesuai implementasi terbaru.
- **`docs/openapi.yaml`** — Memperjelas dokumentasi strategi sinkronisasi dan persyaratan nama token `master-data-api`.

---

## [2026-05-03] — Master Data API (M2M Sync)

### ✅ Added
- **`GET /api/master/users`** — Endpoint baru untuk sinkronisasi master data pegawai & mitra oleh aplikasi client (Machine-to-Machine). Mendukung filter `type`, `period`, `updated_after`, dan pagination hingga 500 record per halaman.
- **`GET /api/master/users/{id}`** — Endpoint detail satu pengguna untuk validasi real-time.
- **`MasterUserController`** — Controller baru di `app/Http/Controllers/Api/` untuk menangani request Master Data API.
- **`MasterUserResource`** — API Resource transformer yang menstandarisasi format response (termasuk `avatar_url` sebagai full URL absolut).
- **`ValidateMasterToken` Middleware** — Middleware keamanan tambahan yang memastikan endpoint hanya bisa diakses via Personal Access Token bernama `master-data-api`, bukan token OAuth flow biasa.
- **Kolom `period`, `contract_start`, `contract_end`** — Kolom baru di tabel `users` untuk mendukung siklus kontrak mitra tahunan dan mitra adhoc (sensus, dll).
- **`docs/openapi.yaml`** — OpenAPI 3.0.3 spec lengkap — machine-readable, dapat diimpor ke Postman/Swagger UI, dan dapat dibaca langsung oleh AI assistant.
- **`docs/API_MASTER_USERS.md`** — Panduan integrasi developer-friendly termasuk implementasi referensi Laravel lengkap (Console Command, Scheduler, Filament button, Model Scope).

### 🔄 Changed
- **`routes/api.php`** — Penambahan route group `/master/*` dengan middleware bertingkat: `auth:api` + `master.token` + `throttle:60,1`.
- **`app/Models/User.php`** — Penambahan `period`, `contract_start`, `contract_end` ke `$fillable` dan `casts` (date).
- **`bootstrap/app.php`** — Registrasi alias middleware `master.token`.
- **`GEMINI.md`** — Penambahan section "Master Data API" agar AI assistant otomatis mengetahui keberadaan endpoint ini.

---

## Refaktor Arsitektur Akses Kontrol & Stabilisasi SSO

### ✅ Added (Ditambahkan)

| # | Perubahan | Manfaat / Tujuan |
|---|-----------|-----------------|
| 1 | **Custom `OAuthAuthorizationController`** — Controller otorisasi OAuth kustom yang menggantikan binding N3XT0R, diinjeksi via `AppServiceProvider`. | Memberikan kontrol penuh atas alur otorisasi: mendukung `prompt=login`, `prompt=none`, `prompt=consent`, dan penolakan akses (403) langsung di dalam controller — tanpa ketergantungan pada view kustom vendor. |
| 2 | **Sistem Access Rule berbasis Policy** — Model `ClientAccessRule`, enum `ClientAccessPolicy` (Open/Restricted), enum `AccessRuleType` (User/SipetraRole/IdentityType), dan service `AccessRuleResolver`. | Menggantikan pendekatan whitelist per-user (`ClientUserAccess`) di origin. Kini admin bisa mengatur akses dengan aturan *granular*: per user, per role Sipetra, atau per tipe identitas (pegawai/mitra). Lebih fleksibel dan scalable. |
| 3 | **Endpoint API `/api/user/me`** — Via `UserProfileResource` (JSON Resource) yang mengembalikan profil lengkap (identity + organization) dalam satu panggilan. | Klien cukup memanggil satu endpoint untuk mendapatkan data lengkap, tanpa harus memanggil 3 endpoint terpisah (`/user`, `/user/identity`, `/user/organization`). Mengurangi jumlah HTTP request. |
| 4 | **`TokenDisplayNameResolver`** — Service yang me-resolve nama user pemilik token (bukan pemilik client). | Memperbaiki tampilan daftar token di Filament Admin agar menunjukkan nama user yang sebenarnya login, bukan nama admin pemilik aplikasi OAuth client. |
| 5 | **Custom Filament `ClientResource` & `TokenResource`** — Extend dari N3XT0R `BaseClientResource` dan `BaseTokenResource` dengan form kustom, relation manager, dan kolom resolver. | Menambahkan UI manajemen Access Rules langsung di halaman detail client, serta daftar token dengan nama user yang akurat. |
| 6 | **`AccessRulesRelationManager`** — Filament Relation Manager di halaman Client. | Admin dapat menambah/menghapus aturan akses klien langsung dari form detail OAuth Client. |
| 7 | **`ClientTokensRelationManager`** — Daftar token aktif per-client. | Monitoring token yang terbit untuk setiap aplikasi klien. |
| 8 | **`ClientPolicy`** — Authorization Policy untuk model `Passport\Client`. | Kontrol akses CRUD client via Filament Shield; `super_admin` dan `admin` mendapat akses penuh, role lain harus memiliki permission eksplisit. |
| 9 | **Custom Livewire Login Page** (`App\Livewire\Auth\Login`) — Halaman login SSO kustom mendukung NIP (lama & baru) dan Email. | Menggantikan login bawaan Filament untuk user biasa. Pegawai bisa login dengan NIP, admin diarahkan ke `/admin/login`. Mendukung alur OAuth redirect setelah login. |
| 10 | **Web Routes** (`/login`, `/dashboard`, `/logout`) — Rute web eksplisit untuk SSO. | Rute `/login` yang bernama diperlukan agar middleware `guest` dan Passport `redirect_to_login` berfungsi. Rute `/dashboard` sebagai fallback bagi user yang login langsung (bukan via OAuth). |
| 11 | **Migrasi `access_policy` dan `client_access_rules`** — 2 migrasi baru menggantikan migrasi `client_roles` dan `client_user_accesses` dari origin. | Skema database yang lebih bersih untuk model akses kontrol yang baru. |
| 12 | **`PassportScopeSeeder`** — Seeder untuk data master scope. | Memastikan scope `profile:read`, `identity:read`, dll. tersedia di database untuk modul N3XT0R. |
| 13 | **Direktori `docs/`** — Berisi Dokumentasi terpusat untuk developer klien dan QA. |
| 14 | **Dependensi `webbingbrasil/filament-copyactions`** — Package tombol copy di Filament. | Memudahkan admin menyalin credensial client langsung dari panel. |
| 15 | **Dependensi `pestphp/pest-plugin-livewire`** — Plugin Pest untuk testing Livewire komponen. | Mendukung testing komponen login Livewire. |
| 16 | **Test Suite Lengkap** — 5 file test baru: `OAuthFlowTest`, `ClientAccessControlTest`, `UserProfileApiTest`, `ClientScopeGrantCompatibilityTest`, `TokenDisplayNameResolverTest`. | Coverage testing untuk seluruh alur SSO: otorisasi, token exchange, API, access control, dan resolver. |

---

### 🔄 Changed (Diubah)

| # | Perubahan | Alasan / Manfaat |
|---|-----------|-----------------|
| 1 | **Model `User.canAccessPanel()`** — Hanya `super_admin`, `admin`, `operator` yang bisa akses panel admin.
| 2 | **Model `PassportClient`** — Class dipindah dari `App\Models\PassportClient` (extend `N3XT0R\...\Client`) ke `App\Models\Passport\Client` (tetap extend N3XT0R). | Namespace lebih terorganisir. Ditambah method `accessRules()`, cast `access_policy`, dan override `getMorphClass()` untuk backward compatibility scope grants. |
| 3 | **`AppServiceProvider`** — Binding diubah total. | Origin: bind halaman CRUD N3XT0R (`CreateClient`, `EditClient`, `ViewClient`). Sekarang: bind `OAuthAuthorizationController` dan register `ClientPolicy` via Gate. Scopes dan token lifetime dipindah ke sini (konsisten dengan Laravel Passport best practice). |
| 4 | **`/api/user` Response** — Menghapus field `client_role` dari response `/api/user`. | `client_role` (origin) bergantung pada `ClientUserAccess` yang sudah di-deprecate. Fitur role per-client akan di-deliver ulang di rilis berikutnya melalui `ClientAccessRule`. |
| 5 | **UserApiController** — Dihapus referensi ke `$request->user()->token()->client_id` dan `clientRoleFor()`. | Menghilangkan coupling ke model `ClientUserAccess` yang sudah tidak ada. |
| 6 | **Authorization View** — Dari closure yang menampilkan `oauth.rejected` (origin) menjadi static blade `vendor.passport.authorize`. | Consent screen kini menggunakan view Blade standar Passport. Penolakan akses ditangani di controller (`OAuthAuthorizationController`), bukan di view. |
| 7 | **`composer.json`** — Menghapus `filament/spatie-laravel-settings-plugin` dan `spatie/laravel-settings`. Menambah `webbingbrasil/filament-copyactions`. | Fitur System Settings untuk session via `.env` yang lebih sederhana. |
| 8 | **README.md** — Disederhanakan menjadi panduan instalasi inti. | Changelog detail dipindah ke file `docs/CHANGELOG.md` terpisah. |

---

### 🗑️ Removed (Dihapus)

| # | Komponen yang Dihapus | Alasan |
|---|----------------------|--------|
| 1 | **Model `ClientRole`** — Tabel dan model untuk mendefinisikan role per-client. | Digantikan oleh `ClientAccessRule` dengan `AccessRuleType::SipetraRole`. Lebih fleksibel karena bisa menggunakan role Spatie yang sudah ada. |
| 2 | **Model `ClientUserAccess`** — Tabel dan model whitelist user per-client. | Digantikan oleh `ClientAccessRule` dengan `AccessRuleType::User` atau `AccessRuleType::IdentityType`. |
| 3 | **`User::clientRoleFor()`** — Method resolusi role klien di model User. | Fitur ini masih dalam rencana tahap 2 dan akan diimplementasi melalui mekanisme baru. |
| 4 | **`SystemSettings` + Spatie Settings** — Halaman pengaturan session lifetime di panel admin. | Kompleksitas tambahan yang tidak diperlukan di fase awal. Konfigurasi cukup via `.env`. |
| 5 | **Migrasi `plain_secret_to_oauth_clients`** — Kolom `plain_secret` di tabel `oauth_clients`. | Penyimpanan secret dalam plaintext dihapus demi keamanan. Secret hanya ditampilkan sekali saat pembuatan. |
| 6 | **Migrasi `create_settings_table`** — Tabel untuk Spatie Settings. | Tidak diperlukan karena modul settings dihapus. |
| 7 | **Migrasi `client_roles` dan `client_user_accesses`** — Tabel untuk model akses kontrol lama. | Digantikan oleh migrasi `access_policy` dan `client_access_rules`. |
| 8 | **File-file dokumen di root** — `arsitektur_portal_sso.md`, `panduan_integrasi_klien.md`, `sso_fase2_planning.md`, `fix_admin.php`. | Dokumentasi dipindahkan ke `docs/`. Script perbaikan sementara dihapus. |
| 9 | **Binding N3XT0R Pages** — Override `CreateClient`, `EditClient`, `ViewClient` di `AppServiceProvider`. | Digantikan oleh custom `ClientResource` Filament yang mendefinisikan halaman-halamannya sendiri. |
| 10 | **`MitraSeeder` dan `PegawaiSeeder`** (terpisah) — Origin punya seeder terpisah untuk Mitra dan Pegawai. | Sudah digabung ke `ImportUsersSeeder` yang lebih efisien dengan batch processing. |

---

### 🐛 Fixed (Diperbaiki)

| # | Bug yang Diperbaiki | Detail |
|---|-------------------|--------|
| 1 | **Token Identity Mismatch** — Token OAuth dikaitkan dengan owner client (Super Admin) alih-alih user yang login. | Diperbaiki dengan `TokenDisplayNameResolver` yang lookup `user_id` dari tabel token, bukan `owner_id` dari client. |
| 2 | **Panel Access Blocking** — User biasa (Pegawai/Mitra) tidak bisa login SSO karena `canAccessPanel()` memblokir mereka. | Diperbaiki dengan membuat rute login terpisah (`/login`) untuk SSO, sementara panel admin (`/admin/login`) tetap restricted. |
| 3 | **`Route [login] not defined`** — Error karena tidak ada named route `login`. | Diperbaiki dengan menambahkan rute web eksplisit di `web.php`. |
| 4 | **Morph Type Compatibility** — Scope grants gagal resolve karena morph type berubah saat model di-extend. | Diperbaiki dengan override `getMorphClass()` di `App\Models\Passport\Client` yang mengembalikan class N3XT0R. |
| 5 | **Browser Back-Button Fix** — Penggunaan middleware `NoCacheHeaders` untuk mencegah browser menampilkan halaman lama setelah logout. | Keamanan data: mencegah user melihat data user lain dari cache browser saat menekan tombol Back setelah logout. |

---

## [2026-04-23] — Client Wizard & Settings Plugin Fixes

### 🐛 Fixed
- **Missing "Izin User" Tab** — Mengaktifkan kembali konfigurasi `use_database_scopes = true` pada `config/passport-authorization-core.php` untuk memunculkan kembali tahap 2 (Izin User) pada form pembuatan Klien SSO.
- **SettingsPage not found** — Menginstall dependensi `filament/spatie-laravel-settings-plugin` (v5.6) yang menyebabkan error saat mengakses menu System Settings.
- **Missing Table settings** — Menjalankan `php artisan migrate` untuk membuat tabel `settings` dan migrasi klien lainnya setelah pull kode terbaru.

### 🔄 Changed
- **Edit Scopes UI** — Mengganti Relation Manager (tabel yang harus diinput satu per satu) dengan `ScopeCheckboxList` pada form Edit Client agar lebih praktis seperti saat Create.
- **Dropdown User UI** — Menambahkan keterangan "Ketik untuk mencari..." pada form Nilai Aturan (Access Rules) tipe User agar tidak membingungkan pengguna (sebelumnya hanya muncul "Tidak ada data").

---

## [2026-04-20] — Documentation Update

### 🔄 Changed
- **`Panduan_Integrasi_SSO.md`** — Menambahkan scope `email:read` pada contoh konfigurasi Laravel Socialite. Hal ini memastikan klien mendapatkan data email secara eksplisit sesuai dengan standar OAuth2.

---

## [2026-04-16] — User Profile & Branding Updates

### ✅ Added
- **User Avatar Support** — Implementasi fitur upload avatar pada CRUD User di Panel Admin. User kini dapat mengunggah foto profil yang tersimpan di storage (`avatars/`).
- **Filament `HasAvatar` Integration** — Mengintegrasikan model `User` dengan interface `HasAvatar` milik Filament sehingga foto profil muncul di pojok kanan atas dashboard.
- **Dark Mode Logo Support** — Penambahan logo khusus untuk dark mode (`logoBpsDemakOren.png`) pada halaman login dan dashboard untuk meningkatkan estetika visual saat menggunakan tema gelap.
- **Image Preview in Tables** — Penambahan kolom avatar (lingkaran) pada tabel daftar user untuk memudahkan identifikasi visual.

---

## [2026-04-15] — Security Update & Documentation

### ✅ Added
- **`NoCacheHeaders` Middleware** — Middleware global (didaftarkan di group `web`) untuk mengirim header anti-cache guna mencegah kebocoran data via browser cache.
- **Integrasi Keamanan di Dokumentasi** — Penambahan Bagian 11 di `Panduan_Integrasi_SSO.md` mengenai penanganan browser cache dan penggunaan null-safe operator di Blade.
- **Enhanced OAuth Client Creation UX** — Implementasi **3-Step Wizard** pada form pendaftaran klien baru. Admin kini dapat mengonfigurasi *Access Policy* dan *Access Rules* dalam satu alur kerja. Field *Access Rules* kini mendukung **Multi-select**, memudahkan penambahan banyak user/role sekaligus.
- **Active Token Counter** — Penambahan badge pada menu "Tokens" di sidebar yang secara real-time menunjukkan jumlah token yang aktif (belum dicabut).
- **Sinkronisasi Waktu & Lokal** — Konfigurasi zona waktu ke **Asia/Jakarta (WIB)** dan lokal ke **Bahasa Indonesia (id)** untuk memastikan seluruh pencatatan waktu dan format pesan sesuai dengan standar lokal.

### 🐛 Fixed
- **Undefined method 'update'** — Penambahan type hint PHPDoc di controller klien untuk memperbaiki warning statis.
- **Attempt to read property "name" on null** — Implementasi **null-safe operator** (`?->`) di dasbor server dan penanganan user null di controller klien untuk mencegah crash saat navigasi Back setelah logout.



---

## [2026-04-13] — Rilis (Baseline)

### Added
- System Settings Page (session lifetime via Spatie Settings)
- Alur pembuatan OAuth Client yang disederhanakan (redirect URI auto-generated)
- Penyimpanan & tampilan Client Secret permanen (`plain_secret`)
- Tombol "Copy .ENV" di halaman detail client
- Client Access Control per-user (`ClientRole`, `ClientUserAccess`)
- Halaman penolakan akses (`oauth.rejected`)
- API `client_role` di endpoint `/api/user`
- Migrasi `activity_log` dari Spatie ActivityLog

### Fixed
- TypeError pada halaman View Client (inheritance model)
- Copy .ENV menghasilkan null (Eloquent `$hidden` blocking)
- Class Action Not Found (namespace Filament v5)

---

## [Awal — Pre-2026-04-08]

### Added
- `ImportUsersSeeder` untuk batch import Pegawai dan Mitra
- Import Pegawai dari `pegawai.json`
- Import Mitra dari file Excel menggunakan `phpoffice/phpspreadsheet`
- Kolom identitas baru pada tabel `users` (NIP, SOBAT ID, dsb.)
- Integrasi Filament Shield dan Laravel Passport
- Custom landing page dengan dark theme
