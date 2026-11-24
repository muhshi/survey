<?php

use App\Filament\Resources\Survey\Pages\DesignSurvey;
use App\Models\Group;
use App\Models\Survey;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    foreach (['ViewAny:Survey', 'View:Survey', 'Update:Survey', 'Create:Survey'] as $perm) {
        $p = Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        $role->givePermissionTo($p);
    }
});

test('design survey page renders with header actions and title', function () {
    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole('super_admin');
    $survey = Survey::factory()->create([
        'title' => 'Survei Kepuasan Layanan 2026',
        'is_quiz' => false,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->get("/admin/survey/surveys/{$survey->id}/design")
        ->assertOk()
        ->assertSee('Desain Kuesioner')
        ->assertSee('Survei Kepuasan Layanan 2026')
        ->assertSee('Kembali ke Edit')
        ->assertSee('Pratinjau Kuesioner')
        ->assertSee('Simpan Desain');
});

test('design survey page can save schema updates', function () {
    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole('super_admin');
    $survey = Survey::factory()->create([
        'title' => 'Survei Update Schema',
        'schema' => ['title' => 'Initial Title'],
    ]);

    $newSchema = [
        'title' => 'Updated Schema Title',
        'pages' => [
            [
                'name' => 'page1',
                'elements' => [
                    ['type' => 'text', 'name' => 'question1', 'title' => 'Pertanyaan 1'],
                ],
            ],
        ],
    ];

    Livewire::actingAs($admin)
        ->test(DesignSurvey::class, ['record' => $survey->id])
        ->fillForm([
            'schema' => $newSchema,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $survey->refresh();
    expect($survey->schema)->toEqual($newSchema);
});

test('admin can preview questionnaire even if survey is restricted to groups or inactive', function () {
    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole('super_admin');

    $survey = Survey::factory()->create([
        'title' => 'Survei Tertutup Khusus Grup',
        'is_active' => false,
    ]);
    $group = Group::create(['name' => 'Kelompok Khusus']);
    $survey->groups()->attach($group);

    // Regular user is denied
    $regularUser = User::factory()->create(['is_active' => true]);
    $this->actingAs($regularUser)
        ->get(route('survey.show', ['survey' => $survey->slug]))
        ->assertOk()
        ->assertSee('Survei Tidak Aktif');

    // Admin can preview without access denied
    $this->actingAs($admin)
        ->get(route('survey.show', ['survey' => $survey->slug, 'preview' => 1]))
        ->assertOk()
        ->assertSee('Mode Pratinjau Pembuat Kuesioner')
        ->assertSee($survey->title);
});
