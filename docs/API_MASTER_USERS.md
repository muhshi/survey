# Panduan Integrasi: Sipetra Master Data API

> 📄 **Kontrak resmi (machine-readable):** [`openapi.yaml`](./openapi.yaml)
> Dokumen ini adalah panduan *developer-friendly* untuk tim yang membangun aplikasi client.

---

## Daftar Isi
- [Gambaran Umum](#gambaran-umum)
- [Autentikasi M2M](#autentikasi-m2m)
- [Endpoint](#endpoint)
- [Strategi Sinkronisasi](#strategi-sinkronisasi)
- [Mitigasi Edge Case](#mitigasi-edge-case)
- [Implementasi Referensi (Laravel)](#implementasi-referensi-laravel)
- [Checklist Integrasi Client Baru](#checklist-integrasi-client-baru)

---

## Gambaran Umum

API ini memungkinkan aplikasi client menarik (**pull**) master data pegawai dan mitra dari Sipetra secara berkala, tanpa harus menunggu pengguna melakukan login SSO.

```
Sipetra (SSO Server)               Aplikasi Client
────────────────────               ───────────────
GET /api/master/users   ◄──────    php artisan sync:users
      │                            (dijalankan via Laravel Scheduler, jam 06:00)
      ▼
 Pagination loop
      │
      ▼                            Simpan `synced_at` untuk sync berikutnya
 updateOrCreate di tabel users lokal
```

**Kapan menggunakan API ini?**
- Dropdown pemilihan peserta rapat, penerima surat, penandatangan
- Fitur yang butuh daftar pegawai/mitra meski user belum login SSO
- Laporan yang membutuhkan data organisasi lengkap

---

## Autentikasi M2M

Semua endpoint menggunakan **Personal Access Token** (PAT) Passport.
Ini berbeda dari token OAuth SSO user biasa — ini adalah token server-to-server.

```http
Authorization: Bearer {SIPETRA_API_TOKEN}
Accept: application/json
```

### Cara Mendapatkan Token

1. Hubungi administrator Sipetra
2. Minta dibuatkan token dengan informasi: nama aplikasi client + deskripsi penggunaan
3. Admin akan menjalankan:
   ```php
   // Di Tinker Sipetra
   $user = App\Models\User::where('identity_type', 'admin')->first();
   $token = $user->createToken('master-data-api');
   echo $token->accessToken; // Salin ini
   ```
4. Simpan token di `.env` aplikasi client:
   ```env
   SIPETRA_API_TOKEN="token_yang_diberikan_admin"
   SIPETRA_BASE_URL="https://bpsdemak.com"
   ```

> ⚠️ **Satu aplikasi client = satu token.** Jika token bocor, admin dapat merevoke-nya tanpa mempengaruhi client lain. Jangan commit token ke Git.

---

## Endpoint

### `GET /api/master/users` — Daftar Pengguna

**Query Parameters:**

| Parameter | Tipe | Default | Keterangan |
|---|---|---|---|
| `type` | `pegawai` \| `mitra` | *(semua)* | Filter tipe pengguna |
| `period` | string | *(semua)* | Filter mitra per periode: `"2026"`, `"sensus_ekonomi_2026"` |
| `updated_after` | datetime ISO 8601 | *(semua)* | Hanya data yang berubah setelah waktu ini |
| `per_page` | integer | `100` | Maksimum 500 per halaman |
| `page` | integer | `1` | Nomor halaman |

**Contoh Request:**
```bash
# Full sync semua data
curl -H "Authorization: Bearer TOKEN" \
     "https://bpsdemak.com/api/master/users?per_page=500"

# Incremental sync
curl -H "Authorization: Bearer TOKEN" \
     "https://bpsdemak.com/api/master/users?updated_after=2026-05-02T06:00:00%2B07:00"

# Hanya mitra tahun 2026
curl -H "Authorization: Bearer TOKEN" \
     "https://bpsdemak.com/api/master/users?type=mitra&period=2026"
```

**Struktur Response:**
```json
{
  "data": [
    {
      "sipetra_id": "1",
      "name": "Budi Santoso",
      "email": "budi.santoso@bps.go.id",
      "avatar_url": "https://bpsdemak.com/storage/avatars/budi.jpg",
      "identity_type": "pegawai",
      "nip": "199001012020011001",
      "nip_baru": "199001012020011001",
      "sobat_id": null,
      "nomor_hp": "08123456789",
      "jabatan": "Statistisi Ahli Pertama",
      "golongan": "III/a",
      "unit_kerja": "Seksi Distribusi",
      "kd_satker": "33210",
      "gender": "L",
      "is_active": true,
      "period": null,
      "contract_start": null,
      "contract_end": null,
      "updated_at": "2026-04-15T08:30:00+07:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 100,
    "total": 287,
    "from": 1,
    "to": 100
  },
  "synced_at": "2026-05-03T15:00:00+07:00"
}
```

> **Penting:** Simpan nilai `synced_at` dari response sebagai `updated_after` untuk sync berikutnya.

### `GET /api/master/users/{sipetra_id}` — Detail Satu Pengguna

Mengambil data terkini satu pengguna. Berguna untuk validasi real-time.

```bash
curl -H "Authorization: Bearer TOKEN" \
     "https://bpsdemak.com/api/master/users/42"
```

---

## Strategi Sinkronisasi

### Alur Lengkap

```
┌─────────────────────────────────────────────────────────┐
│  Ambil timestamp sync terakhir dari cache               │
│  (null jika belum pernah sync / --full mode)            │
└──────────────────────┬──────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────┐
│  Loop: GET /api/master/users?updated_after=...          │
│        (pagination: page 1, 2, 3, ...)                  │
└──────────────────────┬──────────────────────────────────┘
                       │
                       ▼ Untuk setiap user dalam response:
┌─────────────────────────────────────────────────────────┐
│  User::updateOrCreate(                                  │
│    ['sipetra_id' => $data['sipetra_id']],               │
│    [...semua field termasuk is_active...]               │
│  )                                                      │
└──────────────────────┬──────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────┐
│  Simpan nilai `synced_at` dari response terakhir        │
│  ke cache sebagai referensi sync berikutnya             │
└─────────────────────────────────────────────────────────┘
```

### Aturan Penanganan Data

| Kondisi | Tindakan di Sisi Client |
|---|---|
| `sipetra_id` baru | INSERT record baru |
| `sipetra_id` sudah ada | UPDATE semua field |
| `is_active: false` diterima | UPDATE `is_active = false` — **JANGAN DELETE** |
| Mitra, `period` berubah | UPDATE `period`, `contract_start`, `contract_end` |

---

## Mitigasi Edge Case

### Pegawai Pensiun / Pindah

Sipetra akan set `is_active: false`. Saat sync berikutnya, client mengupdate flag lokal.

```php
// Filter di semua dropdown — hanya tampilkan user aktif
User::where('is_active', true)->where('identity_type', 'pegawai')->get();
```

### Mitra — Pergantian Tahunan

Setiap awal tahun, Sipetra mengupdate `period`, `contract_start`, `contract_end` dan `is_active`.
Client tidak perlu logika khusus — cukup jalankan sync, `updateOrCreate` menangani selebihnya.

### Mitra Adhoc (Sensus, dll.)

Filter via `period` dengan kode kegiatan:
```bash
GET /api/master/users?type=mitra&period=sensus_ekonomi_2026
```

### Mitra Lama Ikut Kontrak Baru

`sipetra_id` tetap sama — Sipetra hanya update `period` dan `contract_end`.
`updateOrCreate` di client otomatis menangani ini.

---

## Implementasi Referensi (Laravel)

### 1. Konfigurasi `.env` & `config/services.php`

```env
SIPETRA_BASE_URL="https://bpsdemak.com"
SIPETRA_API_TOKEN="token_dari_admin_sipetra"
```

```php
// config/services.php
'sipetra' => [
    'base_url'  => env('SIPETRA_BASE_URL'),
    'api_token' => env('SIPETRA_API_TOKEN'),
    // ... config SSO lainnya
],
```

### 2. Migrasi Kolom Tambahan di Client

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('sipetra_id')->nullable()->unique();
    $table->boolean('is_active')->default(true);
    $table->string('period', 50)->nullable();
    $table->date('contract_start')->nullable();
    $table->date('contract_end')->nullable();
    $table->string('identity_type')->nullable();
    // ... kolom lain sesuai kebutuhan
});
```

### 3. Console Command `sync:users`

```php
// app/Console/Commands/SyncUsersFromSipetra.php
<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncUsersFromSipetra extends Command
{
    protected $signature = 'sync:users {--full : Abaikan timestamp, sync semua data}';
    protected $description = 'Sinkronisasi master data pengguna dari Sipetra';

    public function handle(): int
    {
        $baseUrl  = config('services.sipetra.base_url');
        $token    = config('services.sipetra.api_token');
        $lastSync = $this->option('full') ? null : Cache::get('sipetra_last_synced_at');

        $this->info($lastSync ? "Incremental sync sejak: {$lastSync}" : 'Full sync...');

        $page = 1;
        $created = $updated = 0;

        do {
            $response = Http::withToken($token)
                ->acceptJson()
                ->get("{$baseUrl}/api/master/users", array_filter([
                    'page'          => $page,
                    'per_page'      => 500,
                    'updated_after' => $lastSync,
                ]));

            if ($response->failed()) {
                $this->error("Gagal: HTTP {$response->status()}");
                Log::error('sync:users failed', ['status' => $response->status()]);
                return self::FAILURE;
            }

            $payload  = $response->json();
            $lastPage = $payload['meta']['last_page'];
            $syncedAt = $payload['synced_at'];

            foreach ($payload['data'] as $data) {
                $result = User::updateOrCreate(
                    ['sipetra_id' => $data['sipetra_id']],
                    [
                        'name'           => $data['name'],
                        'email'          => $data['email'],
                        'avatar_url'     => $data['avatar_url'],
                        'identity_type'  => $data['identity_type'],
                        'nip'            => $data['nip'],
                        'nip_baru'       => $data['nip_baru'],
                        'jabatan'        => $data['jabatan'],
                        'golongan'       => $data['golongan'],
                        'unit_kerja'     => $data['unit_kerja'],
                        'kd_satker'      => $data['kd_satker'],
                        'nomor_hp'       => $data['nomor_hp'],
                        'jenis_kelamin'  => $data['gender'],
                        'is_active'      => $data['is_active'],
                        'period'         => $data['period'],
                        'contract_start' => $data['contract_start'],
                        'contract_end'   => $data['contract_end'],
                    ]
                );

                $result->wasRecentlyCreated ? $created++ : $updated++;
            }

            $this->line("  Halaman {$page}/{$lastPage} selesai.");
            $page++;

        } while ($page <= $lastPage);

        Cache::put('sipetra_last_synced_at', $syncedAt, now()->addDays(30));

        $this->info("✅ Selesai. Dibuat: {$created}, Diupdate: {$updated}.");

        return self::SUCCESS;
    }
}
```

### 4. Scheduler

```php
// routes/console.php
Schedule::command('sync:users')->dailyAt('06:00');
```

### 5. Scope di Model User Client

```php
// app/Models/User.php
public function scopeActive($query)
{
    return $query->where('is_active', true);
}

public function scopePegawai($query)
{
    return $query->active()->where('identity_type', 'pegawai');
}

public function scopeMitra($query, ?string $period = null)
{
    $q = $query->active()->where('identity_type', 'mitra');
    return $period ? $q->where('period', $period) : $q;
}
```

### 6. Tombol Sync Manual di Filament

Sangat disarankan menggunakan **Background Job** untuk sinkronisasi guna menghindari *browser timeout* jika data yang ditarik sangat banyak.

```php
// 1. Buat Job: php artisan make:job SyncUsersJob
// app/Jobs/SyncUsersJob.php
public function handle(): void
{
    Artisan::call('sync:users', ['--full' => true]);
}

// 2. Gunakan di Filament Action (ListUsers.php atau Widget)
use App\Jobs\SyncUsersJob;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

Action::make('sync_users')
    ->label('Sync dari Sipetra')
    ->icon('heroicon-o-arrow-path')
    ->color('info')
    ->requiresConfirmation()
    ->action(function () {
        SyncUsersJob::dispatch();
        
        Notification::make()
            ->title('Sinkronisasi Dimulai')
            ->body('Proses berjalan di latar belakang. Data akan terupdate otomatis.')
            ->info()
            ->send();
    }),
```

---

## Checklist Integrasi Client Baru

### Di Sipetra (Tim IT)
- [ ] Generate Personal Access Token bernama `master-data-api` untuk akun admin
- [ ] Kirimkan token ke PIC aplikasi client secara aman (bukan via chat plain text)

### Di Aplikasi Client (Tim Pengembang)
- [ ] Tambahkan `SIPETRA_API_TOKEN` dan `SIPETRA_BASE_URL` ke `.env`
- [ ] Update `config/services.php`
- [ ] Buat migrasi kolom: `sipetra_id`, `is_active`, `period`, `contract_start`, `contract_end`
- [ ] Buat Console Command `sync:users` (lihat kode referensi di atas)
- [ ] Daftarkan ke Scheduler: `dailyAt('06:00')`
- [ ] Test: `php artisan sync:users --full`
- [ ] Verifikasi data terisi di tabel `users`
- [ ] Gunakan scope `active()` di semua dropdown

---

*Dokumen ini dikelola oleh Tim IT BPS Kab. Demak. Lihat `openapi.yaml` untuk spesifikasi teknis lengkap.*
