<?php

use App\Models\Survey;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    foreach (['ViewAny:Survey', 'View:Survey', 'Update:Survey', 'Create:Survey'] as $perm) {
        $p = Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        $role->givePermissionTo($p);
    }
});

test('survey creator scripts are not injected in dashboard', function () {
    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole('super_admin');

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertDontSee('survey-creator-core.min.js');
});

test('survey creator scripts are loaded on design survey page', function () {
    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole('super_admin');
    $survey = Survey::factory()->create(['title' => 'Survei Uji Coba']);

    $this->actingAs($admin)
        ->get("/admin/survey/surveys/{$survey->id}/design")
        ->assertOk()
        ->assertSee('survey-creator-core');
});
