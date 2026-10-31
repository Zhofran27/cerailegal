<?php

namespace Database\Factories;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    protected $model = Faq::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question' => fake()->sentence(6) . '?',
            'answer' => fake()->paragraphs(2, true),
            'category' => fake()->randomElement(['Prosedur Gugatan', 'Syarat Dokumen', 'Biaya Perkara', 'Hak Asuh & Nafkah', 'Glosarium']),
            'is_published' => true,
            'order' => fake()->numberBetween(0, 100),
        ];
    }
}

