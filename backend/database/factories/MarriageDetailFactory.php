<?php

namespace Database\Factories;

use App\Models\LegalCase;
use App\Models\MarriageDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarriageDetail>
 */
class MarriageDetailFactory extends Factory
{
    protected $model = MarriageDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hasChildren = fake()->boolean(70);

        return [
            'case_id' => LegalCase::factory(),
            'marriage_date' => fake()->dateTimeBetween('-20 years', '-2 years')->format('Y-m-d'),
            'marriage_certificate_number' => fake()->numerify('###/##/XI/2020'),
            'kua_or_civil_registry' => 'KUA Kecamatan ' . fake()->city(),
            'has_children' => $hasChildren,
            'children_count' => $hasChildren ? fake()->numberBetween(1, 4) : 0,
            'separation_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'last_address' => fake()->address(),
        ];
    }
}

