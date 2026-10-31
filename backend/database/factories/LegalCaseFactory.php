<?php

namespace Database\Factories;

use App\Models\Court;
use App\Models\LegalCase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LegalCase>
 */
class LegalCaseFactory extends Factory
{
    protected $model = LegalCase::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'court_id' => Court::factory(),
            'case_number' => fake()->unique()->numerify('###/Pdt.G/2026/PA.###'),
            'reason' => fake()->paragraphs(2, true),
            'status' => fake()->randomElement(['draft', 'submitted', 'in_review', 'approved', 'rejected']),
        ];
    }
}

