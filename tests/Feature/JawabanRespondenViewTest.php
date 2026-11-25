<?php

use App\Exports\QuizRecapExport;
use App\Filament\Resources\JawabanResponden\Pages\ListJawabanResponden;
use App\Models\JawabanResponden;
use App\Models\Kategori;
use App\Models\Survey;
use App\Models\User;
use App\Services\QuizRecapService;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    Artisan::call('migrate');

    Kategori::firstOrCreate(['id' => 1], ['name' => 'Sensus', 'slug' => 'sensus']);

    $this->admin = User::factory()->create([
        'email' => 'admin_test@demak.go.id',
    ]);
    Permission::firstOrCreate(['name' => 'ViewAny:JawabanResponden']);
    $this->admin->givePermissionTo('ViewAny:JawabanResponden');

    Artisan::call('survey:import-gform-se2026');
});

test('admin can render list jawaban responden with single export button and tabs', function () {
    $this->actingAs($this->admin);

    $survey = Survey::where('slug', 'konfirmasi-pendataan-se2026')->first();

    Livewire::test(ListJawabanResponden::class)
        ->assertOk()
        ->assertSee('Grafik Ringkasan')
        ->assertSee('Tabel Response')
        ->assertSee('Unduh Excel')
        ->assertActionDoesNotExist('export')
        ->assertSet('selectedSurveyId', $survey->id)
        ->assertSet('currentTab', 'grafik')
        ->call('setTab', 'tabel')
        ->assertSet('currentTab', 'tabel');
});

test('direct jawaban responden export route downloads valid xlsx', function () {
    $this->actingAs($this->admin);

    $survey = Survey::where('slug', 'konfirmasi-pendataan-se2026')->first();

    $response = $this->get(route('jawaban-responden.export', ['survey_id' => $survey->id]));

    $response->assertOk();
    $response->assertHeader('content-disposition');
    expect($response->headers->get('content-disposition'))->toContain('.xlsx');
});

test('export url updates when changing selectedSurveyId filter', function () {
    $this->actingAs($this->admin);

    $survey1 = Survey::where('slug', 'konfirmasi-pendataan-se2026')->first();
    $survey2 = Survey::create([
        'kategori_id' => 1,
        'title' => 'Survei Kedua Untuk Filter Test',
        'slug' => 'survei-kedua-filter-test',
        'schema' => ['pages' => []],
        'is_active' => true,
        'mode' => 'multi',
    ]);
    $survey2->forceFill(['created_at' => now()->addMinute()])->save();

    JawabanResponden::create([
        'survey_id' => $survey2->id,
        'user_id' => $this->admin->id,
        'payload' => ['nama_lengkap' => 'Test User'],
        'submitted_at' => now(),
    ]);

    $component = Livewire::test(ListJawabanResponden::class)
        ->assertSet('selectedSurveyId', $survey2->id)
        ->set('selectedSurveyId', $survey1->id)
        ->assertSet('selectedSurveyId', $survey1->id);

    $expectedUrl = route('jawaban-responden.export', ['survey_ids' => [$survey1->id]]);
    expect($component->instance()->currentSurvey?->id)->toBe($survey1->id)
        ->and($component->instance()->getExportUrl())->toBe($expectedUrl);
});

test('quiz recap deduplicates multiple attempts per user taking highest score and fastest tie-breaker', function () {
    $this->actingAs($this->admin);

    $quiz = Survey::create([
        'kategori_id' => 1,
        'title' => 'Kuis Pendalaman Test',
        'slug' => 'kuis-pendalaman-test',
        'is_quiz' => true,
        'is_active' => true,
        'mode' => 'single',
        'settings' => ['passing_score' => 70],
        'schema' => [
            'pages' => [[
                'name' => 'p1',
                'elements' => [
                    [
                        'type' => 'dropdown',
                        'name' => 'kelas',
                        'title' => 'Kelas',
                        'choices' => [
                            ['value' => 'Kelas A', 'text' => 'Kelas A (Inda: Muna)'],
                            ['value' => 'Kelas D', 'text' => 'Kelas D (Inda: Nur)'],
                        ],
                    ],
                    ['type' => 'radiogroup', 'name' => 'soal_1', 'title' => 'Soal 1', 'choices' => ['a', 'b']],
                ],
            ]],
        ],
    ]);

    $userNailul = User::factory()->create(['name' => 'Nailul Muna']);
    $userFast = User::factory()->create(['name' => 'Peserta Cepat']);
    $userSlow = User::factory()->create(['name' => 'Peserta Lambat']);
    $userClassA = User::factory()->create(['name' => 'Juara Kelas A']);

    // Nailul Muna submits twice in Kelas D (50 earlier, 45 later -> must appear ONCE with 50)
    JawabanResponden::create([
        'survey_id' => $quiz->id,
        'user_id' => $userNailul->id,
        'score' => 50,
        'payload' => ['kelas' => 'Kelas D', 'soal_1' => 'a'],
        'submitted_at' => now()->subMinutes(20),
    ]);
    JawabanResponden::create([
        'survey_id' => $quiz->id,
        'user_id' => $userNailul->id,
        'score' => 45,
        'payload' => ['kelas' => 'Kelas D', 'soal_1' => 'b'],
        'submitted_at' => now()->subMinutes(5),
    ]);

    // Tie-breaker in Kelas D: same score 100, userFast submitted earlier than userSlow
    JawabanResponden::create([
        'survey_id' => $quiz->id,
        'user_id' => $userSlow->id,
        'score' => 100,
        'payload' => ['kelas' => 'Kelas D', 'soal_1' => 'a'],
        'submitted_at' => now()->subMinutes(10),
    ]);
    JawabanResponden::create([
        'survey_id' => $quiz->id,
        'user_id' => $userFast->id,
        'score' => 100,
        'payload' => ['kelas' => 'Kelas D', 'soal_1' => 'a'],
        'submitted_at' => now()->subMinutes(15),
    ]);

    // Kelas A participant
    JawabanResponden::create([
        'survey_id' => $quiz->id,
        'user_id' => $userClassA->id,
        'score' => 90,
        'payload' => ['kelas' => 'Kelas A', 'soal_1' => 'a'],
        'submitted_at' => now()->subMinutes(12),
    ]);

    $service = app(QuizRecapService::class);
    $recap = $service->getBestParticipantAttempts($quiz);

    // 4 unique participants total (5 raw submissions)
    expect($recap)->toHaveCount(4);

    $nailulEntry = $recap->firstWhere('name', 'Nailul Muna');
    expect($nailulEntry['best_score'])->toBe(50.0)
        ->and($nailulEntry['attempts_count'])->toBe(2)
        ->and($nailulEntry['class_rank'])->toBe(3);

    $leaderboard = $service->getTopPerformersByClass($quiz, 3);
    expect($leaderboard)->toHaveCount(2);

    $kelasD = collect($leaderboard)->firstWhere('kelas', 'Kelas D');
    expect($kelasD['winners'])->toHaveCount(3)
        ->and($kelasD['winners'][0]['name'])->toBe('Peserta Cepat')
        ->and($kelasD['winners'][1]['name'])->toBe('Peserta Lambat')
        ->and($kelasD['winners'][2]['name'])->toBe('Nailul Muna');

    // Verify QuizRecapExport collection matches deduplicated 4 rows
    $export = new QuizRecapExport($quiz, ['nama_peserta', 'kelas', 'peringkat_kelas', 'best_score']);
    expect($export->collection())->toHaveCount(4);

    // Verify Livewire page displays leaderboard and unique count
    Livewire::test(ListJawabanResponden::class, ['selectedSurveyId' => $quiz->id])
        ->assertOk()
        ->assertSee('Peringkat 3 Terbaik Per Kelas')
        ->assertSee('Peserta Cepat')
        ->assertSee('Nailul Muna')
        ->assertSee('Kelas D');

    // Verify direct export route uses QuizRecap filename for quiz
    $response = $this->get(route('jawaban-responden.export', ['survey_ids' => [$quiz->id]]));
    $response->assertOk();
    expect($response->headers->get('content-disposition'))->toContain('Rekap_Kuis_');
});
