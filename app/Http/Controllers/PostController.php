<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
//    public function index()
//    {
//        //
//    }
//
    public function store(Request $request)
    {
        $validate = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
        ]);

        $post = new Post();
        $post->fill($validate);

        $post->state = Post::STAT_DRAFT;
        $post->user_id = Auth::logoutOtherDevices();
        $post->save();

        return response()->json($post, 201);
    }
//
//    public function show(Post $post)
//    {
//        //
//    }
//
//    public function update(Request $request, Post $post)
//    {
//        //
//    }
//
//    public function destroy(Post $post)
//    {
//        //
//    }
}
