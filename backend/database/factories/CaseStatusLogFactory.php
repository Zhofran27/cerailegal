<?php

namespace Database\Factories;

use App\Models\CaseStatusLog;
use App\Models\LegalCase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CaseStatusLog>
 */
class CaseStatusLogFactory extends Factory
{
    protected $model = CaseStatusLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'case_id' => LegalCase::factory(),
            'status' => fake()->randomElement(['draft', 'submitted', 'in_review', 'approved', 'rejected']),
            'notes' => fake()->sentence(),
            'changed_by_admin_id' => User::factory(),
        ];
    }
}

