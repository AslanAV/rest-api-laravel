<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => 'Post Name',
            'content' => '<p>Post Content</p>',
            'state' => Post::STATE_DRAFT,
            'user_id' => fn() => User::factory()->create()->id,
        ];
    }
}
