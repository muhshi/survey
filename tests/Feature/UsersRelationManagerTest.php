<?php

use App\Filament\Resources\Groups\Pages\EditGroup;
use App\Filament\Resources\Groups\RelationManagers\UsersRelationManager;
use App\Models\Group;
use App\Models\User;
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

test('users relation manager displays group members', function () {
    $group = Group::create(['name' => 'Kelompok 1']);
    $user = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
    $group->users()->attach($user);

    $admin = User::factory()->create(['is_active' => true]);
    $this->actingAs($admin);

    Livewire::test(UsersRelationManager::class, [
        'ownerRecord' => $group,
        'pageClass' => EditGroup::class,
    ])
        ->assertSuccessful()
        ->assertSee('John Doe')
        ->assertSee('john@example.com');
});
