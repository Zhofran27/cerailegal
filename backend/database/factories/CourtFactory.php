<?php

namespace Database\Factories;

use App\Models\Court;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Court>
 */
class CourtFactory extends Factory
{
    protected $model = Court::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $city = fake()->city();

        return [
            'name' => 'Pengadilan Agama ' . $city,
            'code' => 'PA-' . strtoupper(fake()->unique()->lexify('???')),
            'address' => fake()->address(),
            'city' => $city,
            'phone' => fake()->phoneNumber(),
        ];
    }
}

