<?php

use App\Enums\SurveyMode;
use App\Models\Group;
use App\Models\JawabanResponden;
use App\Models\Survey;
use App\Models\User;
use App\Services\SurveyAccessService;
use App\Services\SurveySubmissionService;

test('survey access service denies inactive survey', function () {
    $service = app(SurveyAccessService::class);
    $survey = Survey::factory()->create(['is_active' => false]);

    $result = $service->checkAccess($survey, null);

    expect($result['allowed'])->toBeFalse()
        ->and($result['title'])->toBe('Survei Tidak Aktif');
});

test('survey access service checks group membership', function () {
    $service = app(SurveyAccessService::class);
    $survey = Survey::factory()->create(['is_active' => true]);
    $group = Group::create(['name' => 'Grup A']);
    $survey->groups()->attach($group);

    $user = User::factory()->create();

    // User is not in group
    $result = $service->checkAccess($survey, $user);
    expect($result['allowed'])->toBeFalse()
        ->and($result['title'])->toBe('Akses Ditolak');

    // User attached to group
    $group->users()->attach($user);
    $result = $service->checkAccess($survey, $user);
    expect($result['allowed'])->toBeTrue();
});

test('survey submission service calculates quiz score accurately', function () {
    $service = app(SurveySubmissionService::class);
    $survey = Survey::factory()->create([
        'is_quiz' => true,
        'schema' => [
            'pages' => [
                [
                    'name' => 'page1',
                    'elements' => [
                        [
                            'name' => 'q1',
                            'correctAnswer' => 'b',
                            'score' => 2,
                        ],
                        [
                            'name' => 'q2',
                            'correctAnswer' => 'a',
                            'score' => 2,
                        ],
                    ],
                ],
            ],
        ],
    ]);

    // All correct => 100
    $score1 = $service->calculateScore($survey, ['q1' => 'b', 'q2' => 'a']);
    expect($score1)->toBe(100.0);

    // Half correct => 50
    $score2 = $service->calculateScore($survey, ['q1' => 'b', 'q2' => 'c']);
    expect($score2)->toBe(50.0);

    // None correct => 0
    $score3 = $service->calculateScore($survey, ['q1' => 'x', 'q2' => 'y']);
    expect($score3)->toBe(0.0);
});

test('survey submission service enforces retake quota for single mode quiz', function () {
    $service = app(SurveySubmissionService::class);
    $user = User::factory()->create();
    $survey = Survey::factory()->create([
        'mode' => SurveyMode::Single,
        'is_quiz' => true,
        'settings' => [
            'allow_retake' => true,
            'max_retakes' => 2,
            'passing_score' => 70,
        ],
    ]);

    // Initial state: eligible
    $eligibility1 = $service->checkEligibility($survey, $user);
    expect($eligibility1['can_submit'])->toBeTrue();

    // 1st attempt
    JawabanResponden::create([
        'survey_id' => $survey->id,
        'user_id' => $user->id,
        'payload' => [],
        'score' => 50,
        'submitted_at' => now(),
    ]);

    // Still eligible because max_retakes is 2
    $eligibility2 = $service->checkEligibility($survey, $user);
    expect($eligibility2['can_submit'])->toBeTrue();

    // 2nd attempt
    JawabanResponden::create([
        'survey_id' => $survey->id,
        'user_id' => $user->id,
        'payload' => [],
        'score' => 80,
        'submitted_at' => now(),
    ]);

    // Quota exhausted
    $eligibility3 = $service->checkEligibility($survey, $user);
    expect($eligibility3['can_submit'])->toBeFalse()
        ->and($eligibility3['already_submitted'])->toBeTrue()
        ->and($eligibility3['message'])->toContain('Batas maksimal pengulangan kuis');
});

test('survey controller handles submit endpoint properly', function () {
    $user = User::factory()->create();
    $survey = Survey::factory()->create([
        'mode' => SurveyMode::Multi,
        'is_active' => true,
    ]);

    $this->actingAs($user);

    $response = $this->postJson(route('survey.submit', $survey->slug), [
        'payload' => ['nama' => 'Budi', 'jawaban' => 'Baik'],
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Jawaban Anda telah berhasil disimpan.',
        ]);

    $this->assertDatabaseHas('jawaban_responden', [
        'survey_id' => $survey->id,
        'user_id' => $user->id,
    ]);
});
