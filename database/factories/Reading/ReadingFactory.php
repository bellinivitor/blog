<?php

namespace Database\Factories\Reading;

use App\Models\Reading\Reading;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reading>
 */
class ReadingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Reading>
     */
    protected $model = Reading::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(3),
            'url' => fake()->unique()->url(),
        ];
    }
}
