# Optimasi Performa dan Pengembangan Fitur Survei BPS Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Mengoptimalkan kinerja panel admin dan runner survei, meningkatkan keandalan database dan asset loading, serta menambahkan fitur Auto-Save (Draft Recovery) dan Bukti Pengisian (Printable Receipt ber-QR Code).

**Architecture:**
- Isolasi aset berat SurveyJS Creator khusus ke halaman `DesignSurvey` di Filament v5.
- Penambahan indeks database (migration non-destructive) pada `master_wilayah` dan `jawaban_responden`, serta caching API wilayah.
- Optimasi N+1 query pada kolom Jawaban di `SurveyTable`.
- Pengelolaan aset SurveyJS lokal (`public/vendor/surveyjs/`) dengan fallback CDN.
- Implementasi auto-save client-side (LocalStorage) dengan prompt recovery di `show.blade.php`.
- Pembuatan view tanda terima pengisian (Receipt) berstandar BPS dengan QR Code verifikasi berbasis `chillerlan/php-qrcode`.

**Tech Stack:**
- Laravel 13, PHP 8.4
- Filament v5 & Livewire v4
- SurveyJS (Core, UI, Creator)
- SQLite / MySQL compatible migrations
- chillerlan/php-qrcode (sudah ada di vendor)
- Pest PHP 4

**Spec:** Dokumen Observasi dan Analisis (`observasi-optimalisasi-pengembangan-survey.md`)

## Global Constraints

- Sesuai aturan global pengguna: **Dilarang keras melakukan drop, truncate, atau menghapus database/tabel**. Seluruh migrasi harus aditif/non-destruktif.
- Seluruh kode PHP harus mengikuti format Pint (`vendor/bin/pint --dirty --format agent`).
- Seluruh pengujian menggunakan Pest (`php artisan test --compact`).
- Tidak boleh mengubah atau menambah dependency `composer.json` tanpa persetujuan eksplisit.

## Review Focus

1. Pengecekan halaman admin non-desain (Dashboard, User, Kategori) tidak memicu error ketika script SurveyJS dipindahkan dari `AdminPanelProvider`.
2. Keandalan auto-save LocalStorage saat ganti halaman atau survei berstatus `single`/`editable`/`multi`.
3. Keamanan akses receipt pengisian (hanya pemilik submission atau submission anonim yang memegang token verifikasi).
4. Penanganan query wilayah dengan cache agar tidak terjadi cache poisoning bila parameter kosong.
5. Runner survei tetap berfungsi normal baik saat aset lokal tersedia maupun bila fallback ke CDN.

---

### Task 1: Isolasi Aset SurveyJS Creator di Admin Panel

**Files:**
- Modify: `app/Providers/Filament/AdminPanelProvider.php:80-110`
- Modify: `app/Filament/Resources/Survey/Pages/DesignSurvey.php`
- Test: `tests/Feature/AdminPanelAssetIsolationTest.php`

**Interfaces:**
- Consumes: Filament Panel Hooks
- Produces: Dynamic script/style injection scoped only to `DesignSurvey` page

- [ ] **Step 1: Tulis failing test untuk isolasi aset SurveyJS di panel admin**

```php
test('survey creator scripts are not injected in dashboard', function () {
    $admin = \App\Models\User::factory()->create(['is_active' => true]);
    $admin->assignRole('super_admin');

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertDontSee('survey-creator-core.min.js');
});

test('survey creator scripts are loaded on design survey page', function () {
    $admin = \App\Models\User::factory()->create(['is_active' => true]);
    $admin->assignRole('super_admin');
    $survey = \App\Models\Survey::factory()->create();

    $this->actingAs($admin)
        ->get("/admin/surveys/{$survey->id}/design")
        ->assertOk()
        ->assertSee('survey-creator-core');
});
```

- [ ] **Step 2: Jalankan test untuk memverifikasi kegagalan (failing)**

Run: `php artisan test --filter=AdminPanelAssetIsolationTest`
Expected: FAIL (karena script creator saat ini masih ada di dashboard)

- [ ] **Step 3: Implementasi pemindahan injeksi aset di `AdminPanelProvider.php` dan `DesignSurvey.php`**

Hapus hook `panels::head.end` yang memuat SurveyJS Creator secara global dari `AdminPanelProvider.php`.
Tambahkan method `getExtraHeaderScripts()` dan `getExtraStylesheets()` atau render hook terdedikasi di `DesignSurvey.php` agar hanya di-load saat mendesain kuesioner.

- [ ] **Step 4: Jalankan test untuk memverifikasi keberhasilan (passing)**

Run: `php artisan test --filter=AdminPanelAssetIsolationTest`
Expected: PASS

- [ ] **Step 5: Format dan Commit**

```bash
git add app/Providers/Filament/AdminPanelProvider.php app/Filament/Resources/Survey/Pages/DesignSurvey.php tests/Feature/AdminPanelAssetIsolationTest.php
git commit -m "perf: scope surveyjs creator assets only to design survey page"
```

---

### Task 2: Database Indexing, Caching Wilayah, & Optimasi Query SurveyTable

**Files:**
- Create: `database/migrations/2026_10_31_180000_add_performance_indexes_to_surveys_and_wilayah.php`
- Modify: `app/Http/Controllers/Api/RegionController.php`
- Modify: `app/Filament/Resources/Survey/Tables/SurveyTable.php`
- Test: `tests/Feature/RegionControllerOptimizationTest.php`

**Interfaces:**
- Consumes: `MasterWilayah`, `JawabanResponden`, `Cache`
- Produces: Indexed lookups and cached JSON responses for regions

- [ ] **Step 1: Tulis failing test untuk caching wilayah**

```php
test('region endpoints return cached data', function () {
    \Illuminate\Support\Facades\Cache::flush();
    \App\Models\MasterWilayah::create([
        'idsubsls' => '33210100010001',
        'nmkec' => 'DEMAK',
        'kdkec' => '010',
        'nmdesa' => 'BINTORO',
        'kddesa' => '001',
        'nmsls' => 'RT 01 RW 01',
        'kdsls' => '0001',
    ]);

    $res1 = $this->getJson('/api/regions/kecamatan');
    $res1->assertOk()->assertJsonFragment(['value' => 'DEMAK']);
    expect(\Illuminate\Support\Facades\Cache::has('regions_kecamatan'))->toBeTrue();

    $res2 = $this->getJson('/api/regions/desa?kecamatan=DEMAK');
    $res2->assertOk()->assertJsonFragment(['value' => 'BINTORO']);
    expect(\Illuminate\Support\Facades\Cache::has('regions_desa_DEMAK'))->toBeTrue();
});
```

- [ ] **Step 2: Jalankan test untuk memverifikasi kegagalan**

Run: `php artisan test --filter=RegionControllerOptimizationTest`
Expected: FAIL (Cache belum tersimpan)

- [ ] **Step 3: Buat migrasi index baru & implementasikan caching + optimasi query**

1. Jalankan `php artisan make:migration add_performance_indexes_to_surveys_and_wilayah`.
   Tambahkan index:
   - `master_wilayah`: `['nmkec', 'nmdesa']`
   - `jawaban_responden`: `['survey_id', 'user_id']` dan `['survey_id', 'submitted_at']`
2. Update `RegionController.php` dengan `Cache::rememberForever()` untuk kecamatan dan desa (dengan cache key dinamis berdasarkan input).
3. Batasi subSls di `RegionController.php` agar tidak memuat semua jawaban responden tanpa batas.
4. Di `SurveyTable.php`, optimalkan perhitungan `jawaban_respondens_count` agar tidak memicu N+1 query.

- [ ] **Step 4: Jalankan migrasi dan verifikasi test**

Run: `php artisan migrate`
Run: `php artisan test --filter=RegionControllerOptimizationTest`
Expected: PASS

- [ ] **Step 5: Format dan Commit**

```bash
git add database/migrations/*performance_indexes* app/Http/Controllers/Api/RegionController.php app/Filament/Resources/Survey/Tables/SurveyTable.php tests/Feature/RegionControllerOptimizationTest.php
git commit -m "perf: add database indexes, cache region endpoints, and optimize survey table counts"
```

---

### Task 3: Local Asset Bundling & Fallback untuk SurveyJS Runner

**Files:**
- Create: `public/vendor/surveyjs/` (salinan file SurveyJS core, UI, dan CSS)
- Modify: `resources/views/survey/show.blade.php`
- Test: `tests/Feature/SurveyRunnerAssetTest.php`

**Interfaces:**
- Consumes: Local files in `public/vendor/surveyjs/`
- Produces: Resilient script loading with CDN fallback

- [ ] **Step 1: Tulis test pengecekan aset runner**

```php
test('survey show view renders resilient surveyjs asset links', function () {
    $survey = \App\Models\Survey::factory()->create([
        'is_active' => true,
        'access_level' => 'public',
        'schema' => ['pages' => [['name' => 'p1', 'elements' => [['type' => 'text', 'name' => 'q1']]]]],
    ]);

    $this->get("/s/{$survey->slug}")
        ->assertOk()
        ->assertSee('survey.core.min.js')
        ->assertSee('defaultV2.min.css');
});
```

- [ ] **Step 2: Jalankan test**

Run: `php artisan test --filter=SurveyRunnerAssetTest`
Expected: PASS/FAIL check

- [ ] **Step 3: Setup aset lokal SurveyJS dan lengkapi mekanisme fallback**

Unduh / letakkan file minified SurveyJS di `public/vendor/surveyjs/` atau sediakan script loader yang otomatis beralih ke CDN jika asset lokal tidak dapat dimuat, atau sebaliknya.

- [ ] **Step 4: Jalankan test**

Run: `php artisan test --filter=SurveyRunnerAssetTest`
Expected: PASS

- [ ] **Step 5: Format dan Commit**

```bash
git add public/vendor/surveyjs/ resources/views/survey/show.blade.php tests/Feature/SurveyRunnerAssetTest.php
git commit -m "feat: add local surveyjs bundling and resilient asset loading"
```

---

### Task 4: Auto-Save & Draft Recovery di Kuesioner Responden

**Files:**
- Modify: `resources/views/survey/show.blade.php`
- Test: `tests/Feature/SurveyAutoSaveDraftTest.php`

**Interfaces:**
- Consumes: SurveyJS Model events (`onValueChanged`, `onComplete`)
- Produces: LocalStorage key `survey_draft_{survey_id}_{identifier}` and restore UI prompt

- [ ] **Step 1: Tulis test untuk memastikan elemen Auto-Save & Banner Recovery tersedia di Blade**

```php
test('survey show renders draft recovery modal or container', function () {
    $survey = \App\Models\Survey::factory()->create([
        'is_active' => true,
        'access_level' => 'public',
        'schema' => ['pages' => [['name' => 'p1', 'elements' => [['type' => 'text', 'name' => 'q1']]]]],
    ]);

    $this->get("/s/{$survey->slug}")
        ->assertOk()
        ->assertSee('draft-recovery-alert')
        ->assertSee('localStorage');
});
```

- [ ] **Step 2: Jalankan test**

Run: `php artisan test --filter=SurveyAutoSaveDraftTest`
Expected: FAIL

- [ ] **Step 3: Implementasi Auto-Save & Restore di `show.blade.php`**

1. Tambahkan banner/alert elegan di atas kuesioner: `draft-recovery-alert` (tersembunyi secara default).
2. Di script SurveyJS runner:
   - Buat fungsi `getDraftKey()` berbasis ID survei dan ID user (atau 'anon').
   - Saat load, cek apakah ada draft di `localStorage.getItem(getDraftKey())`. Jika ada dan berbeda dari data awal, munculkan banner recovery dengan tombol: "Pulihkan Jawaban" dan "Buang Draf".
   - Daftarkan event listener `model.onValueChanged.add(...)` dengan debounce 500ms untuk menulis jawaban ke `localStorage`.
   - Pada event `model.onComplete.add(...)`, jika submit berhasil, hapus item draft dari `localStorage`.
3. Untuk mode `multi`, perbarui tombol "Isi Kuesioner Baru" agar me-reset model SurveyJS tanpa perlu full page reload.

- [ ] **Step 4: Jalankan test**

Run: `php artisan test --filter=SurveyAutoSaveDraftTest`
Expected: PASS

- [ ] **Step 5: Format dan Commit**

```bash
git add resources/views/survey/show.blade.php tests/Feature/SurveyAutoSaveDraftTest.php
git commit -m "feat: implement client-side auto-save and draft recovery for survey runner"
```

---

### Task 5: Bukti / Tanda Terima Pengisian (Receipt PDF / Printable) dengan QR Code

**Files:**
- Create: `app/Http/Controllers/SurveyReceiptController.php`
- Create: `resources/views/survey/receipt.blade.php`
- Modify: `routes/web.php`
- Modify: `resources/views/survey/show.blade.php`
- Test: `tests/Feature/SurveyReceiptTest.php`

**Interfaces:**
- Consumes: `JawabanResponden`, `Survey`, `chillerlan\QRCode\QRCode`
- Produces: Route `survey.receipt` (`/s/{survey:slug}/receipt/{submission}`), verification QR Code, printable receipt layout with `@media print`

- [ ] **Step 1: Tulis failing test untuk endpoint bukti tanda terima**

```php
test('user can view receipt for their own submission', function () {
    $user = \App\Models\User::factory()->create();
    $survey = \App\Models\Survey::factory()->create(['title' => 'Survei Kepuasan']);
    $submission = \App\Models\JawabanResponden::factory()->create([
        'survey_id' => $survey->id,
        'user_id' => $user->id,
        'payload' => ['nama' => 'Budi', 'jawaban' => 'Puas'],
        'submitted_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('survey.receipt', [$survey, $submission]))
        ->assertOk()
        ->assertSee('Tanda Terima Pengisian')
        ->assertSee('Survei Kepuasan');
});
```

- [ ] **Step 2: Jalankan test**

Run: `php artisan test --filter=SurveyReceiptTest`
Expected: FAIL (route belum terdaftar)

- [ ] **Step 3: Implementasi `SurveyReceiptController`, QR Code generator, dan view `receipt.blade.php`**

1. Daftarkan route `GET /s/{survey:slug}/receipt/{jawaban}` di `routes/web.php`.
2. Buat `SurveyReceiptController@show`:
   - Validasi hak akses (pemilik submission, atau jika submission anonim gunakan token/hash metadata).
   - Generate QR Code verifikasi menggunakan `(new \chillerlan\QRCode\QRCode)->render(route('survey.receipt', [$survey, $jawaban]))` sebagai Base64 image data URI.
3. Buat template `resources/views/survey/receipt.blade.php`:
   - Desain profesional berlogo BPS / Pemkab Demak, memuat nomor referensi pendaftaran, nama responden, waktu submit, ringkasan skor/status lulus (jika kuis), dan QR Code verifikasi.
   - Dilengkapi tombol "Cetak / Simpan PDF" (`window.print()`) dan styling `@media print` presisi tanpa elemen navigasi browser.
4. Tambahkan tautan "Unduh Tanda Terima Pengisian" pada modal hasil submit kuesioner di `show.blade.php`.

- [ ] **Step 4: Jalankan test**

Run: `php artisan test --filter=SurveyReceiptTest`
Expected: PASS

- [ ] **Step 5: Format dan Commit**

```bash
git add app/Http/Controllers/SurveyReceiptController.php resources/views/survey/receipt.blade.php routes/web.php resources/views/survey/show.blade.php tests/Feature/SurveyReceiptTest.php
git commit -m "feat: add printable survey receipt with QR code verification"
```

---

### Task 6: Verifikasi Menyeluruh, Pint, & Update Changelog

**Files:**
- Modify: `README.md`
- Run: Pint & Full Test Suite

- [ ] **Step 1: Jalankan seluruh test suite Pest**

Run: `php artisan test --compact`
Expected: Seluruh test (lama + baru) lulus 100%.

- [ ] **Step 2: Format kode dengan Laravel Pint**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 3: Perbarui Changelog pada README.md**

Tambahkan catatan pembaruan tanggal hari ini (`2026-10-31`) mencakup:
- Isolasi aset SurveyJS Creator pada panel admin
- Indeks performa database dan caching endpoint wilayah
- Fitur Auto-Save & pemulihan draft kuesioner
- Fitur Bukti Tanda Terima Pengisian (Receipt PDF/Print) dengan QR Code verifikasi
- Optimasi reset form mode multi

- [ ] **Step 4: Commit dan Push**

```bash
git add .
git commit -m "feat: performance optimizations, auto-save draft, and printable receipt"
git push
```
