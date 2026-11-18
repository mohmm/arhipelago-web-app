<?php

namespace Database\Factories;

use App\Models\MayaConversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MayaConversation>
 */
class MayaConversationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'last_message_at' => now(),
        ];
    }
}
