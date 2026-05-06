# Panduan Testing SSO SIPETRA

Dokumen ini menjelaskan cara menjalankan dan memahami test suite otomatis SIPETRA, serta panduan testing manual untuk verifikasi end-to-end.

---

## 1. Test Suite Otomatis (Pest PHP)

### Menjalankan Seluruh Test

```bash
# Dari direktori root project sipetra
php artisan test

# Atau via Pest langsung
./vendor/bin/pest
```

### Menjalankan Test Tertentu

```bash
# Test alur OAuth lengkap
php artisan test --filter=OAuthFlowTest

# Test access control
php artisan test --filter=ClientAccessControlTest

# Test API profil user
php artisan test --filter=UserProfileApiTest

# Test kompatibilitas scope grant (morph type)
php artisan test --filter=ClientScopeGrantCompatibilityTest

# Test resolver nama token
php artisan test --filter=TokenDisplayNameResolverTest
```

### Konfigurasi Test

Test menggunakan SQLite in-memory (`phpunit.xml`):
```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

Setiap test file menggunakan `RefreshDatabase` untuk memastikan database bersih di setiap test.

---

## 2. Daftar Test & Cakupan

### `OAuthFlowTest.php` — Alur OAuth End-to-End

| # | Test | Yang diuji |
|---|------|-----------|
| 1 | `shows the authorization consent page` | Halaman consent muncul dengan benar, menampilkan nama client dan `auth_token` hidden field |
| 2 | `approves authorization and gets code` | User menyetujui → redirect ke callback URI dengan `code` dan `state` |
| 3 | `completes full OAuth flow: authorize, token, API` | Alur E2E: consent → approve → token exchange → API call → refresh token. **Catatan**: token exchange mungkin di-skip di test env karena keterbatasan enkripsi key Passport |
| 4 | `accesses /api/user with Passport::actingAs` | Verifikasi endpoint `/api/user` mengembalikan data yang benar menggunakan mock token |
| 5 | `accesses /api/user/identity with scope` | Endpoint `/api/user/identity` hanya bisa diakses dengan scope `identity:read` |
| 6 | `accesses /api/user/organization with scope` | Endpoint `/api/user/organization` hanya bisa diakses dengan scope `organization:read` |
| 7 | `denies access to /api/user without token` | API mengembalikan `401 Unauthorized` jika tanpa token |
| 8 | `rejects invalid redirect uri` | Redirect URI yang tidak terdaftar ditolak dengan `401` |
| 9 | `rejects wrong client secret during token exchange` | Client secret salah → error response |

### `ClientAccessControlTest.php` — Kebijakan Akses Klien

| # | Test | Yang diuji |
|---|------|-----------|
| 1 | `denies oauth authorize when client is restricted and user has no matching rule` | Client `Restricted` tanpa access rule → HTTP 403 + pesan penolakan |
| 2 | `allows oauth authorize when user matches an access rule` | Client `Restricted` + rule `identity_type=pegawai` → consent page berhasil ditampilkan |

### `UserProfileApiTest.php` — API Profil Lengkap

| # | Test | Yang diuji |
|---|------|-----------|
| 1 | `returns the aggregated user profile payload from /api/user/me` | Endpoint `/api/user/me` mengembalikan data lengkap (identity + organization) dalam satu response, dengan mapping field yang benar |

### `ClientScopeGrantCompatibilityTest.php` — Kompatibilitas Morph Type

| # | Test | Yang diuji |
|---|------|-----------|
| 1 | `keeps legacy client morph type so existing scope grants still resolve` | Custom `App\Models\Passport\Client` tetap mengembalikan morph class N3XT0R agar scope grant lama tetap berfungsi |

### `TokenDisplayNameResolverTest.php` — Resolver Nama Token

| # | Test | Yang diuji |
|---|------|-----------|
| 1 | `resolves token display name from the token owner instead of the client owner` | Nama yang ditampilkan di daftar token adalah user yang login (bukan admin pemilik OAuth Client) |

---

## 3. Catatan Penting Testing

### Token Exchange di Test Environment

Test `completes full OAuth flow` menggunakan `markTestSkipped()` jika token exchange gagal. Ini karena:
- Test environment menggunakan SQLite in-memory
- Passport membutuhkan encryption key (RSA) yang mungkin belum di-generate di test env
- Solusi: jalankan `php artisan passport:keys` sebelum test jika ingin test E2E penuh

### Test Menggunakan `Passport::actingAs`

Untuk test API endpoint, gunakan `Passport::actingAs()` daripada alur OAuth penuh:

```php
use Laravel\Passport\Passport;

Passport::actingAs($user, ['profile:read', 'identity:read']);

$this->getJson('/api/user/me')
    ->assertSuccessful()
    ->assertJsonStructure(['id', 'name', 'email', 'profile', 'organization']);
```

Ini lebih reliable karena bypass proses enkripsi token.

### UserFactory States

Gunakan factory states yang tersedia:

```php
// User admin (default)
$admin = User::factory()->create();

// Pegawai dengan data identitas lengkap
$pegawai = User::factory()->pegawai()->create();

// Mitra Statistik
$mitra = User::factory()->mitra()->create();

// User non-aktif
$inactive = User::factory()->inactive()->create();
```

---

## 4. Testing Manual via Browser

### Langkah 1: Siapkan Server

```bash
# Terminal 1 — SIPETRA SSO Server (port 8000)
cd sipetra
php artisan serve

# Terminal 2 — Aplikasi Klien (port 8001)
cd sipetra-client
php artisan serve --port=8001
```

### Langkah 2: Pastikan Passport Keys Tersedia

```bash
cd sipetra
php artisan passport:keys
```

### Langkah 3: Buat OAuth Client (jika belum)

```bash
php artisan passport:client
```

Atau buat melalui panel admin Filament di `http://localhost:8000/admin`.

### Langkah 4: Uji Alur SSO dari Klien

1. Buka `http://localhost:8001`
2. Klik **"Login dengan SIPETRA SSO"**
3. Browser akan redirect ke `http://localhost:8000/login`
4. Login sebagai pegawai (NIP/Password) atau mitra (Email/Password)
5. Jika consent page muncul, klik **Authorize**
6. Browser redirect kembali ke klien → **Dashboard** muncul dengan data profil

### Langkah 5: Uji API via cURL/Postman

#### a. Dapatkan Authorization Code

```
GET http://localhost:8000/oauth/authorize
    ?client_id=YOUR_CLIENT_ID
    &redirect_uri=http://localhost:8001/auth/sipetra/callback
    &response_type=code
    &scope=profile:read identity:read organization:read
    &state=test123
```

#### b. Tukar Code dengan Token

```bash
curl -X POST http://localhost:8000/oauth/token \
  -H "Accept: application/json" \
  -d "grant_type=authorization_code" \
  -d "client_id=YOUR_CLIENT_ID" \
  -d "client_secret=YOUR_CLIENT_SECRET" \
  -d "redirect_uri=http://localhost:8001/auth/sipetra/callback" \
  -d "code=AUTH_CODE"
```

#### c. Akses API Profil

```bash
# Profil lengkap (rekomendasi)
curl http://localhost:8000/api/user/me \
  -H "Authorization: Bearer ACCESS_TOKEN" \
  -H "Accept: application/json"

# Profil dasar
curl http://localhost:8000/api/user \
  -H "Authorization: Bearer ACCESS_TOKEN"

# Data identitas
curl http://localhost:8000/api/user/identity \
  -H "Authorization: Bearer ACCESS_TOKEN"

# Data organisasi
curl http://localhost:8000/api/user/organization \
  -H "Authorization: Bearer ACCESS_TOKEN"
```

---

## 5. Checklist Verifikasi

Gunakan checklist ini untuk memastikan semua komponen berjalan:

- [ ] `php artisan test` — Semua test hijau (PASS)
- [ ] Login NIP pegawai berhasil di `/login`
- [ ] Login email mitra berhasil di `/login`
- [ ] Admin diarahkan ke `/admin/login` (bukan `/login`)
- [ ] OAuth authorize redirect ke consent/skip
- [ ] Client `Restricted` menolak user tanpa access rule (403)
- [ ] Client `Open` mengizinkan semua user aktif
- [ ] Token exchange menghasilkan `access_token`
- [ ] `/api/user/me` mengembalikan data lengkap
- [ ] `/api/user/identity` membutuhkan scope `identity:read`
- [ ] `/api/user/organization` membutuhkan scope `organization:read`
- [ ] Token expired → 401 Unauthorized
- [ ] Refresh token menghasilkan token baru
- [ ] Client secret salah → error response
- [ ] Redirect URI tidak terdaftar → 401
