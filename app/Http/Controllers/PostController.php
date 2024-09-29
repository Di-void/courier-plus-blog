<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Post;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::withCount('likes')->with('comments')->get();

        return response()->json(['message' => 'success', 'data' => ['posts' => $posts]], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Log::channel('stderr')->info('Request input', ['input' => $input]);

        $validated = $request->validate([
            'title' => ['required', 'string'],
            'content' => ['required', 'string'],
            'blog_id' => ['required', 'numeric'],
            'image_uri' => ['required', 'url:http,https']
        ]);

        // check if blog exists
        $blog_id = $validated['blog_id'];
        $blog = Blog::find($blog_id);

        if (!$blog) {
            return response()->json(['error' => "Resource with id '{$blog_id}' not found", 'resource' => 'blogs'], RESPONSE::HTTP_BAD_REQUEST);
        }

        $user_id = Auth::id();
        $validated['user_id'] = $user_id;

        Log::info('Creating new Post: {ctx}', ['ctx' => $validated]);

        $post = Post::create($validated);

        $ctx = [
            'user_id' => $user_id,
            'data' => $post
        ];

        Log::info('Created new Post: {ctx}', ['ctx' => $ctx]);


        return response()->json(['message' => 'success', 'data' => $post], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $blog = Post::withCount('likes')->with('comments')->where('id', $id)->firstOrFail();
            return response()->json(['message' => 'success', 'data' => $blog], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->exception_handler($e, $id);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'title' => ['string'],
            'content' => ['string'],
            'image_uri' => ['url:http,https']
        ]);

        try {
            $post = Post::where('id', $id)->withCount('likes')->with('comments')->firstOrFail();

            $subset = collect($validated)->takeWhile(function ($item, $key) {
                return $item !== null;
            })->map(function ($item, $key) use ($post) {
                $post->$key = $item;
                return $item;
            })->all();

            if (collect($subset)->isEmpty()) {
                // Log post update details
                return response()->json(['message' => 'success', 'data' => $post], Response::HTTP_OK);
            }

            $ctx = [
                'user_id' => Auth::id(),
                'data' => $post,
            ];

            Log::info('Updating Post: {ctx}', ['ctx' => $ctx]);

            $post->save();
            $post->refresh()->loadCount('likes');

            return response()->json(['message' => 'success', 'data' => $post], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->exception_handler($e, $id);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $post = Post::where('id', $id)->firstOrFail();

            $ctx = ['user_id' => Auth::id(), 'data' => $post];

            Log::info('Deleting Post: {ctx}', ['ctx' => $ctx]);

            $post->delete();

            return response()->json(['message' => 'success', 'data' => null], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->exception_handler($e, $id);
        }
    }

    private function exception_handler(\Exception $e, $id)
    {
        if ($e instanceof ModelNotFoundException) {
            $ctx = [
                'user_id' => Auth::id(),
                'resource_type' => 'Post',
                'post_id' => $id,
                'timestamps' => now()
            ];
            Log::info('Unknown resource: {ctx}', ['ctx' => $ctx]);

            return response()->json(['error' => ['message' => "Resource with id '{$id}' not found", 'resource' => 'post']], RESPONSE::HTTP_BAD_REQUEST);
        } else {
            $ctx = ['err' => $e->getMessage()];
            Log::debug('Uncaught Exception: {ctx}', ['ctx' => $ctx]);
            throw $e;
        }
    }
}
