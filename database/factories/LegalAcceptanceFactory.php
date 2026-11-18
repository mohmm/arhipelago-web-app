<?php

namespace Database\Factories;

use App\Models\LegalAcceptance;
use App\Models\LegalDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LegalAcceptance>
 */
class LegalAcceptanceFactory extends Factory
{
    protected $model = LegalAcceptance::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'slug' => LegalDocument::TERMS,
            'version' => '1.0',
            'body_sha256' => hash('sha256', $this->faker->paragraph()),
            'locale' => 'ro',
            'context' => LegalAcceptance::CONTEXT_REGISTRATION,
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'accepted_at' => now(),
        ];
    }
}
