<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostLike;
// use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PostLikeController extends Controller
{
    public function store(string $id)
    {
        try {
            $post = Post::where('id', $id)->firstOrFail();

            $user_id = Auth::id();

            PostLike::create(['user_id' => $user_id, 'post_id' => $post->id]);

            return response()->json(['message' => 'success', 'data' => null], Response::HTTP_OK);
        } catch (\Exception $e) {

            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json(['error' => ['message' => "Resource with id '{$id}' not found", 'resource' => 'posts']], RESPONSE::HTTP_BAD_REQUEST);
            }

            if ($e instanceof \Illuminate\Database\UniqueConstraintViolationException) {
                return response()->json(['error' => ['message' => "Duplicate likes not-allowed", 'resource' => 'posts']], RESPONSE::HTTP_BAD_REQUEST);
            }
        }
    }
}