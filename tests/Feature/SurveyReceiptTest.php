<?php

use App\Models\JawabanResponden;
use App\Models\Survey;
use App\Models\User;

test('user can view receipt for their own submission', function () {
    $user = User::factory()->create(['name' => 'Budi Santoso']);
    $survey = Survey::factory()->create(['title' => 'Survei Kepuasan BPS']);
    $submission = JawabanResponden::factory()->create([
        'survey_id' => $survey->id,
        'user_id' => $user->id,
        'payload' => ['nama' => 'Budi Santoso', 'opd' => 'Dinas Kominfo'],
        'submitted_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('survey.receipt', [$survey, $submission]))
        ->assertOk()
        ->assertSee('Tanda Terima Pengisian')
        ->assertSee('Survei Kepuasan BPS')
        ->assertSee('Budi Santoso')
        ->assertSee('data:image/svg+xml'); // QR Code SVG data URI
});

test('user cannot view another users receipt', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $survey = Survey::factory()->create(['title' => 'Survei Khusus']);
    $submission = JawabanResponden::factory()->create([
        'survey_id' => $survey->id,
        'user_id' => $user1->id,
        'payload' => [],
        'submitted_at' => now(),
    ]);

    $this->actingAs($user2)
        ->get(route('survey.receipt', [$survey, $submission]))
        ->assertForbidden();
});

test('guest can view receipt for anonymous public submission', function () {
    $survey = Survey::factory()->create(['title' => 'Survei Publik BPS', 'access_level' => 'public']);
    $submission = JawabanResponden::factory()->create([
        'survey_id' => $survey->id,
        'user_id' => null,
        'payload' => ['nama_lengkap' => 'Warga Demak'],
        'submitted_at' => now(),
    ]);

    $this->get(route('survey.receipt', [$survey, $submission]))
        ->assertOk()
        ->assertSee('Tanda Terima Pengisian')
        ->assertSee('Survei Publik BPS')
        ->assertSee('Warga Demak');
});
