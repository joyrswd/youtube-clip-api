<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class VideoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->name,
            'youtube_id' => $this->faker->uuid,
            'description' => $this->faker->text,
            'published_at' => $this->faker->dateTime,
            'etag' => $this->faker->uuid,
            'duration' => $this->faker->randomNumber(),
        ];
    }
}
