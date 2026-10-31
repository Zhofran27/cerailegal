<?php

namespace Database\Factories;

use App\Models\LegalCase;
use App\Models\SpouseDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SpouseDetail>
 */
class SpouseDetailFactory extends Factory
{
    protected $model = SpouseDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'case_id' => LegalCase::factory(),
            'full_name' => fake()->name(),
            'nik' => fake()->numerify('3201##############'),
            'birth_date' => fake()->dateTimeBetween('-50 years', '-20 years')->format('Y-m-d'),
            'birth_place' => fake()->city(),
            'gender' => fake()->randomElement(['Laki-laki', 'Perempuan']),
            'religion' => fake()->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']),
            'occupation' => fake()->jobTitle(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
        ];
    }
}

