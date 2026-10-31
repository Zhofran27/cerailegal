<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\LegalCase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'case_id' => LegalCase::factory(),
            'file_path' => 'documents/' . fake()->uuid() . '.pdf',
            'type' => fake()->randomElement(['ktp', 'buku_nikah', 'surat_gugatan', 'kartu_keluarga', 'bukti_transfer']),
            'original_name' => fake()->word() . '.pdf',
            'file_size' => fake()->numberBetween(100000, 5000000),
        ];
    }
}

