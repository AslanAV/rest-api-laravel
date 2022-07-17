<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function testPostStore(): void
    {
        //$this->assertDatabaseCount('posts', 0);
        $user = User::factory()->create();
        $this->actingAs($user);

        $route = route('posts.store');
        $body = [
            'title' => 'Название поста',
            'content' => '<p>Содержимое поста</p>',
        ];

        $response = $this->json('POST', $route, $body);

        $response->assertStatus(201);
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseCount('posts', 1);
    }
}
