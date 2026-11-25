<?php

use App\Models\Group;
use App\Models\Kategori;
use App\Models\Survey;
use App\Models\User;
use App\Services\SurveySubmissionService;
use Database\Seeders\SpkrtQuizSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('pretest spkrt 2027 json schema is valid and adheres to standards', function () {
    $path = database_path('surveys/pretest-spkrt-2027.json');
    expect(file_exists($path))->toBeTrue();

    $schema = json_decode(file_get_contents($path), true);
    expect($schema)->toBeArray()
        ->and($schema['pages'])->toHaveCount(2);

    // Page 1: Identitas
    $pageIdentitas = $schema['pages'][0];
    expect($pageIdentitas['name'])->toBe('page_identitas');
    $elementsIdentitas = collect($pageIdentitas['elements']);
    expect($elementsIdentitas->firstWhere('name', 'nama_lengkap'))->not->toBeNull()
        ->and($elementsIdentitas->firstWhere('name', 'email_peserta'))->not->toBeNull();

    // Page 2: Soal (10 butir)
    $pageSoal = $schema['pages'][1];
    expect($pageSoal['name'])->toBe('page_soal')
        ->and($pageSoal['elements'])->toHaveCount(10);

    $totalScore = 0;
    foreach ($pageSoal['elements'] as $idx => $elem) {
        $num = $idx + 1;
        expect($elem['name'])->toBe("soal_{$num}")
            ->and($elem['type'])->toBe('radiogroup')
            ->and($elem['isRequired'])->toBeTrue()
            ->and($elem['score'])->toBe(10)
            ->and($elem['correctAnswer'])->toBe('a')
            ->and($elem['choices'])->toHaveCount(5);

        $totalScore += $elem['score'];
    }

    expect($totalScore)->toBe(100);
});

test('spkrt quiz seeder registers participants, group, and survey correctly', function () {
    $this->seed(SpkrtQuizSeeder::class);

    // Kategori
    $kategori = Kategori::where('slug', 'pelatihan-spkrt-2027')->first();
    expect($kategori)->not->toBeNull()
        ->and($kategori->name)->toBe('Pelatihan SPKRT 2027');

    // Group
    $group = Group::where('name', 'Petugas SPKRT 2027')->first();
    expect($group)->not->toBeNull();

    // 86 Users (73 mitra + 13 organik)
    expect($group->users()->count())->toBe(86);

    // Check sample mitra & organik
    $mitra = User::where('email', 'elisaananda93@gmail.com')->first();
    expect($mitra)->not->toBeNull()
        ->and($mitra->metadata['sobat_id'])->toBe('337422090468')
        ->and($mitra->metadata['kecamatan_tugas'])->toBe('DEMAK');

    $organik = User::where('email', 'muammal.adib@bps.go.id')->first();
    expect($organik)->not->toBeNull()
        ->and($organik->metadata['kecamatan_tugas'])->toBe('DEMAK')
        ->and($organik->metadata['role'])->toBe('organik');

    // Survey
    $survey = Survey::where('slug', 'pretest-spkrt-2027')->first();
    expect($survey)->not->toBeNull()
        ->and($survey->is_quiz)->toBeTrue()
        ->and($survey->settings['passing_score'])->toBe(70)
        ->and($survey->groups()->first()->id)->toBe($group->id);
});

test('scoring service calculates 100 for all correct answers and handles partial scores', function () {
    $this->seed(SpkrtQuizSeeder::class);
    $survey = Survey::where('slug', 'pretest-spkrt-2027')->first();
    $service = app(SurveySubmissionService::class);

    // Perfect answers (all 'a')
    $perfectPayload = [];
    for ($i = 1; $i <= 10; $i++) {
        $perfectPayload["soal_{$i}"] = 'a';
    }
    expect($service->calculateScore($survey, $perfectPayload))->toBe(100.0);

    // Half answers correct (5 correct 'a', 5 incorrect 'b')
    $halfPayload = [];
    for ($i = 1; $i <= 5; $i++) {
        $halfPayload["soal_{$i}"] = 'a';
    }
    for ($i = 6; $i <= 10; $i++) {
        $halfPayload["soal_{$i}"] = 'b';
    }
    expect($service->calculateScore($survey, $halfPayload))->toBe(50.0);
});

test('pendalaman spkrt 2027 json schema is valid and adheres to standards', function () {
    $path = database_path('surveys/pendalaman-spkrt-2027.json');
    expect(file_exists($path))->toBeTrue();

    $schema = json_decode(file_get_contents($path), true);
    expect($schema)->toBeArray()
        ->and($schema['pages'])->toHaveCount(2);

    // Page 1: Identitas
    $pageIdentitas = $schema['pages'][0];
    expect($pageIdentitas['name'])->toBe('page_identitas');
    $elementsIdentitas = collect($pageIdentitas['elements']);
    expect($elementsIdentitas->firstWhere('name', 'nama_lengkap'))->not->toBeNull()
        ->and($elementsIdentitas->firstWhere('name', 'email_peserta'))->not->toBeNull()
        ->and($elementsIdentitas->firstWhere('name', 'kelas'))->not->toBeNull();

    // Page 2: Soal (20 butir, 5 poin per butir = 100)
    $pageSoal = $schema['pages'][1];
    expect($pageSoal['name'])->toBe('page_soal')
        ->and($pageSoal['elements'])->toHaveCount(20);

    $totalScore = 0;
    foreach ($pageSoal['elements'] as $idx => $elem) {
        $num = $idx + 1;
        expect($elem['name'])->toBe("soal_{$num}")
            ->and($elem['type'])->toBe('radiogroup')
            ->and($elem['isRequired'])->toBeTrue()
            ->and($elem['score'])->toBe(5)
            ->and($elem['correctAnswer'])->toBe('a')
            ->and(count($elem['choices']))->toBeGreaterThanOrEqual(4);

        $totalScore += $elem['score'];
    }

    expect($totalScore)->toBe(100);
});

test('evaluasi penyelenggaraan spkrt 2027 json schema is valid and adheres to standards', function () {
    $path = database_path('surveys/evaluasi-penyelenggaraan-spkrt-2027.json');
    expect(file_exists($path))->toBeTrue();

    $schema = json_decode(file_get_contents($path), true);
    expect($schema)->toBeArray()
        ->and($schema['pages'])->toHaveCount(2);

    // Page 1: Identitas
    $pageIdentitas = $schema['pages'][0];
    expect($pageIdentitas['name'])->toBe('page_identitas');
    $elementsIdentitas = collect($pageIdentitas['elements']);
    expect($elementsIdentitas->firstWhere('name', 'nama_lengkap'))->not->toBeNull()
        ->and($elementsIdentitas->firstWhere('name', 'email_peserta'))->not->toBeNull()
        ->and($elementsIdentitas->firstWhere('name', 'kelas'))->not->toBeNull()
        ->and($elementsIdentitas->firstWhere('name', 'jabatan'))->not->toBeNull();

    // Check Jabatan choices
    $jabatan = $elementsIdentitas->firstWhere('name', 'jabatan');
    expect(collect($jabatan['choices'])->pluck('value')->all())->toContain('PPL', 'PML');

    // Page 2: Evaluasi (5 matrices + 1 comment)
    $pageEvaluasi = $schema['pages'][1];
    expect($pageEvaluasi['name'])->toBe('page_evaluasi')
        ->and($pageEvaluasi['elements'])->toHaveCount(6);

    $matrixNames = [
        'pengajar_inda',
        'waktu_pelatihan_klasikal',
        'ruang_kelas',
        'fasilitas_kamar',
        'penyelenggaraan_pelatihan',
    ];

    foreach ($matrixNames as $matrixName) {
        $elem = collect($pageEvaluasi['elements'])->firstWhere('name', $matrixName);
        expect($elem)->not->toBeNull()
            ->and($elem['type'])->toBe('matrix')
            ->and($elem['isRequired'])->toBeTrue()
            ->and(count($elem['columns']))->toBeGreaterThanOrEqual(4)
            ->and(count($elem['rows']))->toBeGreaterThanOrEqual(2);
    }

    $komentar = collect($pageEvaluasi['elements'])->firstWhere('name', 'komentar_saran_masukan');
    expect($komentar)->not->toBeNull()
        ->and($komentar['type'])->toBe('comment')
        ->and($komentar['isRequired'])->toBeTrue();
});
