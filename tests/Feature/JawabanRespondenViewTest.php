<?php

use App\Filament\Resources\JawabanResponden\Pages\ListJawabanResponden;
use App\Models\JawabanResponden;
use App\Models\Kategori;
use App\Models\Survey;
use App\Models\User;
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

test('admin can render list jawaban responden with survey selector and tabs', function () {
    $this->actingAs($this->admin);

    $survey = Survey::where('slug', 'konfirmasi-pendataan-se2026')->first();

    Livewire::test(ListJawabanResponden::class)
        ->assertOk()
        ->assertSee('Grafik Ringkasan')
        ->assertSee('Tabel Response')
        ->assertSet('selectedSurveyId', $survey->id)
        ->assertSet('currentTab', 'grafik')
        ->call('setTab', 'tabel')
        ->assertSet('currentTab', 'tabel');
});

test('admin can export jawaban responden to excel via direct redirect', function () {
    $this->actingAs($this->admin);

    $survey = Survey::where('slug', 'konfirmasi-pendataan-se2026')->first();

    Livewire::test(ListJawabanResponden::class)
        ->callAction('export', [
            'survey_id' => [$survey->id],
            'fields' => ['survey_title', 'waktu_submit'],
        ])
        ->assertRedirect();
});

test('admin can export with default form state when opening modal', function () {
    $this->actingAs($this->admin);

    Livewire::test(ListJawabanResponden::class)
        ->mountAction('export')
        ->callMountedAction()
        ->assertRedirect();
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
