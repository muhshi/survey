<?php

use App\Filament\Resources\JawabanResponden\Pages\ListJawabanResponden;
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
