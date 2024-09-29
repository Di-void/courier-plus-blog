<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    public function store(Request $request, string $id)
    {
        $clean_content = $request->validate([
            'content' => ['required', 'string']
        ]);

        try {
            $post = Post::where('id', $id)->firstOrFail();

            $user_id = Auth::id();

            $content = [
                'user_id' => $user_id,
                'post_id' => $post->id,
                'content' => $clean_content['content']
            ];

            $new_comment = Comment::create($content)->latest()->first();

            $ctx = [
                'user_id' => $user_id,
                'data' => $new_comment
            ];

            Log::info('New Comment created: {ctx}', ['ctx' => $ctx]);

            return response()->json(['message' => 'success', 'data' => $new_comment], Response::HTTP_OK);
        } catch (\Exception $e) {

            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $ctx = [
                    'user_id' => Auth::id(),
                    'resource_type' => 'Post',
                    'post_id' => $id,
                    'action' => 'Comment',
                    'timestamps' => now()
                ];
                Log::info('Unknown resource: {ctx}', ['ctx' => $ctx]);

                return response()->json(['error' => ['message' => "Resource with id '{$id}' not found", 'resource' => 'posts']], RESPONSE::HTTP_BAD_REQUEST);
            } else {
                $ctx = ['err' => $e->getMessage()];
                Log::debug('Uncaught Exception: {ctx}', ['ctx' => $ctx]);
                throw $e;
            }
        }
    }
}
