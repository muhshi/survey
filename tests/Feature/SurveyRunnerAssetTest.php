<?php

use App\Models\Survey;

test('survey show view renders resilient surveyjs asset links', function () {
    $survey = Survey::factory()->create([
        'is_active' => true,
        'access_level' => 'public',
        'schema' => ['pages' => [['name' => 'p1', 'elements' => [['type' => 'text', 'name' => 'q1']]]]],
    ]);

    $this->get("/s/{$survey->slug}")
        ->assertOk()
        ->assertSee('vendor/surveyjs/survey.core.min.js')
        ->assertSee('defaultV2.min.css');
});
