<?php

use App\Models\Survey;

test('survey show renders draft recovery container and localStorage logic', function () {
    $survey = Survey::factory()->create([
        'is_active' => true,
        'access_level' => 'public',
        'schema' => ['pages' => [['name' => 'p1', 'elements' => [['type' => 'text', 'name' => 'q1']]]]],
    ]);

    $this->get("/s/{$survey->slug}")
        ->assertOk()
        ->assertSee('draft-recovery-alert')
        ->assertSee('localStorage');
});
