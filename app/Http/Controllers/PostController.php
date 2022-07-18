<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PostController extends Controller
{
    public function index(): JsonResponse
    {
        $posts = Post::whereState(Post::STATE_PUBLISHED)->paginate();

        return response()->json($posts);
    }

    public function getUserPosts(User $user): JsonResponse
    {
        $posts = Post::whereUserId($user->id)->paginate();

        return response()->json($posts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
        ]);

        $post = new Post();

        $post->fill($validated);

        $post->state = Post::STATE_DRAFT;
        $post->user_id = Auth::id();

        $post->save();

        return response()->json($post, 201);
    }

    public function show(Post $post): JsonResponse
    {
        if (Post::STATE_DRAFT === $post->state && Auth::id() !== $post->user_id) {
            abort(403, __('У вас нет доступа к этому посту!'));
        }

        return response()->json($post);
    }

    public function update(Request $request, Post $post): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'state' => 'required|int|in:0,1',
        ]);

        $this->checkPostBelongsToAuthUser($post);

        $post->fill($validated);
        $post->save();

        return response()->json($post);
    }

    private function checkPostBelongsToAuthUser(Post $post): void
    {
        if (Auth::id() !== $post->user_id) {
            abort(Response::HTTP_FORBIDDEN, __('Нельзя редактировать чужой пост!'));
        }
    }

    public function destroy(Post $post): \Illuminate\Http\Response
    {
        $this->checkPostBelongsToAuthUser($post);

        if (Post::STATE_PUBLISHED === $post->state) {
            abort(Response::HTTP_FORBIDDEN, __('Нельзя удалить опубликованный пост!'));
        }

        $post->deleteOrFail();

        return response()->noContent();
    }
}
