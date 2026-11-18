<?php

namespace Database\Factories;

use App\Models\LegalDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LegalDocument>
 */
class LegalDocumentFactory extends Factory
{
    protected $model = LegalDocument::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->unique()->words(3, true);

        return [
            'slug' => str($title)->slug()->value(),
            'title_ro' => ucfirst($title),
            'title_en' => ucfirst($title),
            'body_ro' => '## 1. '.ucfirst($this->faker->words(2, true))."\n\n".$this->faker->paragraph(),
            'body_en' => '## 1. '.ucfirst($this->faker->words(2, true))."\n\n".$this->faker->paragraph(),
            'version' => '1.0',
            'effective_date' => now()->subMonth()->toDateString(),
        ];
    }

    public function terms(): static
    {
        return $this->state(['slug' => LegalDocument::TERMS]);
    }

    public function privacy(): static
    {
        return $this->state(['slug' => LegalDocument::PRIVACY]);
    }
}
