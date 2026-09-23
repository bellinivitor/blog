<?php

namespace Database\Factories\Post;

use App\Models\Post\Post;
use App\Models\Post\PostDailyView;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostDailyView>
 */
class PostDailyViewFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<PostDailyView>
     */
    protected $model = PostDailyView::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory()->published(),
            'date' => now(config('blog.timezone'))->toDateString(),
            'views' => fake()->numberBetween(1, 50),
        ];
    }
}
