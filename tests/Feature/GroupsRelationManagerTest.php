<?php

use App\Filament\Resources\Survey\SurveyResource\RelationManagers\GroupsRelationManager;
use App\Filament\Resources\Survey\Pages\EditSurvey;
use App\Models\Survey;
use App\Models\Group;
use App\Models\User;
use App\Models\JawabanResponden;
use Livewire\Livewire;

test('groups relation manager can be rendered and calculates not submitted count correctly', function () {
    // Create survey
    $survey = Survey::factory()->create([
        'title' => 'Pretest Pelatihan',
    ]);

    // Create a group
    $group = Group::create([
        'name' => 'Gelombang I',
    ]);

    // Attach group to survey
    $survey->groups()->attach($group);

    // Create 3 users in the group
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    $group->users()->attach([$user1->id, $user2->id, $user3->id]);

    // user1 has submitted
    JawabanResponden::create([
        'survey_id' => $survey->id,
        'user_id' => $user1->id,
        'payload' => [],
        'submitted_at' => now(),
    ]);

    // Create admin user to log in
    $admin = User::factory()->create([
        'is_active' => true,
    ]);

    $this->actingAs($admin);

    // Render the relation manager
    Livewire::test(GroupsRelationManager::class, [
        'ownerRecord' => $survey,
        'pageClass' => EditSurvey::class,
    ])
    ->assertSuccessful()
    ->assertSee('Gelombang I')
    // Out of 3 members, 1 submitted, so 2 / 3 orang should be displayed
    ->assertSee('2 / 3 orang');
});
