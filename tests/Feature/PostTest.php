<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function testPostStore(): void
    {
        $this->assertDatabaseCount('posts', 0);

        $user = User::factory()->create();
        $this->actingAs($user);

        $route = route('posts.store');
        $body = [
            'title' => 'Название поста',
            'content' => '<p>Содержимое поста</p>',
        ];

        $response = $this->json('POST', $route, $body);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseCount('posts', 1);
    }

    public function testPostUpdateAnotherUser(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $post = Post::factory()->create();

        $route = route('posts.update', [
            'post' => $post->id,
        ]);

        $body = [
            'title' => 'Название поста',
            'content' => '<p>Содержимое поста</p>',
            'state' => Post::STATE_DRAFT,
        ];

        $response = $this->json('PUT', $route, $body);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testPostUpdate(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $post = Post::factory()->create([
            'user_id' => $user->id,
        ]);

        $route = route('posts.update', [
            'post' => $post->id,
        ]);

        $body = [
            'title' => 'Название поста',
            'content' => '<p>Содержимое поста</p>',
            'state' => Post::STATE_DRAFT,
        ];

        $response = $this->json('PUT', $route, $body);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas(
            'posts',
            array_merge($body, [
                'id' => $post->id,
            ])
        );
    }

    public function testPostDestroy(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $post = Post::factory()->create([
            'user_id' => $user->id,
        ]);

        $route = route('posts.destroy', [
            'post' => $post->id,
        ]);

        $response = $this->json('DELETE', $route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }

    public function testPostDestroyUnexistingResource(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $route = route('posts.destroy', [
            'post' => 1,
        ]);

        $response = $this->json('DELETE', $route);

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testPostDestroyAnotherUser(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $route = route('posts.destroy', [
            'post' => Post::factory()->create()->id,
        ]);

        $response = $this->json('DELETE', $route);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testPostDestroyPublished(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'state' => Post::STATE_PUBLISHED,
        ]);

        $route = route('posts.destroy', [
            'post' => $post->id,
        ]);

        $response = $this->json('DELETE', $route);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testGetPublishedPosts(): void
    {
        $publishedCount = 10;
        Post::factory()->count($publishedCount)->create([
            'state' => Post::STATE_PUBLISHED,
        ]);

        $draftCount = 5;
        Post::factory()->count($draftCount)->create([
            'state' => Post::STATE_DRAFT,
        ]);

        $route = route('posts.index');

        $response = $this->json('GET', $route);

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonFragment([
            'total' => $publishedCount,
        ]);
    }

    public function testGetUserPosts(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $userPostsCount = 10;
        Post::factory()->count($userPostsCount)->create([
            'user_id' => $user->id,
        ]);

        $anotherPostsCount = 5;
        Post::factory()->count($anotherPostsCount)->create();

        $route = route('posts.get_user_posts', [
            'user' => $user->id,
        ]);

        $response = $this->json('GET', $route);

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonFragment([
            'total' => $userPostsCount,
        ]);
    }

    public function showPostForGuestProvider(): array
    {
        return [
            'show_published_post_for_guest' => [Post::STATE_PUBLISHED, Response::HTTP_OK],
            'show_draft_post_for_guest' => [Post::STATE_DRAFT, Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @dataProvider showPostForGuestProvider
     */
    public function testShowPostForGuest(int $state, int $statusCode): void
    {
        $post = Post::factory()->create([
            'state' => $state,
        ]);

        $route = route('posts.show', [
            'post' => $post->id,
        ]);

        $response = $this->json('GET', $route);

        $response->assertStatus($statusCode);
    }

    public function testShowDraftPostForUser(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $post = Post::factory()->create([
            'state' => Post::STATE_DRAFT,
            'user_id' => $user->id,
        ]);

        $route = route('posts.show', [
            'post' => $post->id,
        ]);

        $response = $this->json('GET', $route);

        $response->assertStatus(Response::HTTP_OK);
    }

    public function testShowDraftPostForAnotherUser(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $post = Post::factory()->create([
            'state' => Post::STATE_DRAFT,
        ]);

        $route = route('posts.show', [
            'post' => $post->id,
        ]);

        $response = $this->json('GET', $route);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
