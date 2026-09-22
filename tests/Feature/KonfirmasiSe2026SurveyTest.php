<?php

use App\Models\JawabanResponden;
use App\Models\Kategori;
use App\Models\MasterWilayah;
use App\Models\Survey;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    Artisan::call('migrate');

    Kategori::firstOrCreate(['id' => 1], ['name' => 'Sensus', 'slug' => 'sensus']);

    MasterWilayah::create([
        'idsubsls' => '3321070001000100',
        'nmsls' => 'RT 001 RW 001',
        'nama_ketua' => 'Ahmad',
        'nmkec' => 'DEMAK',
        'kdkec' => '070',
        'nmdesa' => 'BINTORO',
        'kddesa' => '001',
        'kdsls' => '0001',
        'kdsubsls' => '00',
    ]);

    Artisan::call('survey:import-gform-se2026');
});

test('public user can access konfirmasi pendataan se2026 runner page', function () {
    $survey = Survey::where('slug', 'konfirmasi-pendataan-se2026')->first();
    expect($survey)->not->toBeNull();

    $response = $this->get(route('survey.show', $survey));
    $response->assertOk();
    $response->assertSee('Konfirmasi Pendataan Sensus Ekonomi 2026 (SE2026)');
});

test('api regions returns cascading desa and sls for demak', function () {
    $desaResponse = $this->get('/api/regions/desa?kecamatan=DEMAK');
    $desaResponse->assertOk();
    $desaResponse->assertJsonFragment(['value' => 'BINTORO']);

    $slsResponse = $this->get('/api/regions/sls?desa=BINTORO&kecamatan=DEMAK');
    $slsResponse->assertOk();
    $data = $slsResponse->json();
    expect(count($data))->toBeGreaterThan(0);
});

test('respondent can submit konfirmasi survey successfully without login', function () {
    $survey = Survey::where('slug', 'konfirmasi-pendataan-se2026')->first();
    expect($survey)->not->toBeNull();

    $payload = [
        'nama_opd' => 'Dinas Komunikasi dan Informatika',
        'nama_lengkap' => 'Budi Santoso, S.Kom',
        'nik' => '3321011204900001',
        'status_kepegawaian' => 'PNS',
        'nama_kk' => 'Budi Santoso',
        'status_pendataan' => 'Belum',
        'kecamatan' => 'DEMAK',
        'desa' => 'BINTORO',
        'sls' => 'RT 001 RW 001',
        'alamat_lengkap' => 'Jl. Pemuda No. 10 Bintoro',
        'no_hp' => '081234567890',
    ];

    $response = $this->postJson(route('survey.submit', $survey), [
        'payload' => $payload,
    ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
    ]);

    $submission = JawabanResponden::where('survey_id', $survey->id)
        ->where('payload->nik', '3321011204900001')
        ->first();

    expect($submission)->not->toBeNull()
        ->and($submission->payload['nama_lengkap'])->toBe('Budi Santoso, S.Kom')
        ->and($submission->payload['nama_opd'])->toBe('Dinas Komunikasi dan Informatika');
});
