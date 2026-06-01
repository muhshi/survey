<?php

use App\Filament\Resources\Groups\RelationManagers\UsersRelationManager;
use App\Filament\Resources\Groups\Pages\EditGroup;
use App\Models\Group;
use App\Models\User;
use App\Models\Survey;
use App\Models\JawabanResponden;
use Livewire\Livewire;

test('users relation manager can be rendered and displays members', function () {
    // Create a group
    $group = Group::create([
        'name' => 'Kelompok 1',
    ]);

    // Create some users
    $user1 = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
    $user2 = User::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);

    // Attach user1 to the group
    $group->users()->attach($user1);

    // Create a mock admin user
    $admin = User::factory()->create([
        'email' => 'admin@example.com',
        'is_active' => true,
    ]);

    // Act as the admin
    $this->actingAs($admin);

    // Render the relation manager
    Livewire::test(UsersRelationManager::class, [
        'ownerRecord' => $group,
        'pageClass' => EditGroup::class,
    ])
    ->assertSuccessful()
    ->assertCanSeeTableRecords([$user1])
    ->assertCanNotSeeTableRecords([$user2]);
});

test('users relation manager shows pretest and pendalaman scores', function () {
    // Create pretest and pendalaman surveys
    Survey::factory()->create([
        'id' => 4,
        'title' => 'Pretest Pelatihan Petugas Lapangan SE2026',
        'is_quiz' => true,
    ]);

    Survey::factory()->create([
        'id' => 5,
        'title' => 'Pendalaman Petugas Lapangan SE2026',
        'is_quiz' => true,
    ]);

    $group = Group::create(['name' => 'Kelompok 1']);
    $user = User::factory()->create(['name' => 'John Doe']);
    $group->users()->attach($user);

    // Create survey submission for pretest (survey_id = 4)
    JawabanResponden::create([
        'survey_id' => 4,
        'user_id' => $user->id,
        'payload' => [],
        'score' => 85.5,
        'submitted_at' => now(),
    ]);

    // Create survey submission for pendalaman (survey_id = 5)
    JawabanResponden::create([
        'survey_id' => 5,
        'user_id' => $user->id,
        'payload' => [],
        'score' => 90.0,
        'submitted_at' => now(),
    ]);

    $admin = User::factory()->create(['is_active' => true]);
    $this->actingAs($admin);

    Livewire::test(UsersRelationManager::class, [
        'ownerRecord' => $group,
        'pageClass' => EditGroup::class,
    ])
    ->assertSuccessful()
    ->assertSee('Selesai (85.5%)')
    ->assertSee('Selesai (90%)');
});
