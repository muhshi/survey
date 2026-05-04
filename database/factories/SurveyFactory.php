<?php

namespace Database\Factories;

use App\Enums\SurveyMode;
use App\Models\Kategori;
use App\Models\Survey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Survey>
 */
class SurveyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kategori_id' => Kategori::factory(),
            'title' => $title = $this->faker->sentence(3),
            'slug' => str($title)->slug(),
            'schema' => [
                'pages' => [
                    [
                        'name' => 'page1',
                        'elements' => [
                            [
                                'type' => 'text',
                                'name' => 'question1',
                                'title' => 'Apa pendapat Anda?',
                            ],
                        ],
                    ],
                ],
            ],
            'mode' => $this->faker->randomElement(SurveyMode::cases()),
            'is_active' => $this->faker->boolean(),
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
        ];
    }
}
