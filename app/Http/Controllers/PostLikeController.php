<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostLike;
// use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PostLikeController extends Controller
{
    public function store(string $id)
    {
        try {
            $post = Post::where('id', $id)->firstOrFail();

            $user_id = Auth::id();

            $ctx = [
                'user_id' => $user_id,
                'data' => $post,
                'timestamps' => now()
            ];

            Log::info('Liking Post', ['data', $ctx]);

            PostLike::create(['user_id' => $user_id, 'post_id' => $post->id]);

            return response()->json(['message' => 'success', 'data' => null], Response::HTTP_OK);
        } catch (\Exception $e) {

            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $ctx = [
                    'user_id' => Auth::id(),
                    'resource_type' => 'Post',
                    'post_id' => $id,
                    'action' => 'Like',
                    'timestamps' => now()
                ];

                Log::info('Unknown resource', ['data' => $ctx]);

                return response()->json(['error' => ['message' => "Resource with id '{$id}' not found", 'resource' => 'posts']], RESPONSE::HTTP_BAD_REQUEST);
            } else if ($e instanceof \Illuminate\Database\UniqueConstraintViolationException) {
                $ctx = [
                    'user_id' => Auth::id(),
                    'resource_type' => 'Post',
                    'post_id' => $id,
                    'action' => 'Like',
                    'timestamps' => now()
                ];

                Log::info('Unknown resource', ['data' => $ctx]);
            } else {
                $ctx = ['err' => $e->getMessage()];
                Log::debug('Uncaught Exception', ['data' => $ctx]);
                throw $e;
            }
        }
    }
}