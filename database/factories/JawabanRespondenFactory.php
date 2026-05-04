<?php

namespace Database\Factories;

use App\Models\JawabanResponden;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JawabanResponden>
 */
class JawabanRespondenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'survey_id' => Survey::factory(),
            'user_id' => User::factory(),
            'payload' => [
                'question1' => $this->faker->sentence(),
            ],
            'metadata' => [
                'ip' => $this->faker->ipv4(),
                'user_agent' => $this->faker->userAgent(),
            ],
            'submitted_at' => now(),
        ];
    }
}
