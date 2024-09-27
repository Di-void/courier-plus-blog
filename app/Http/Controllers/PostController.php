<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Post;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

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
        $blog = Blog::find($validated['blog_id']);

        if (!$blog) {
            return response()->json(['error' => 'Resource not found', 'resource' => 'blogs'], RESPONSE::HTTP_BAD_REQUEST);
        }

        $user_id = Auth::id();
        $validated['user_id'] = $user_id;
        $post = Post::create($validated);
        // Log post creation details

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
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => ['message' => 'Resource not found', 'resource' => 'posts']], RESPONSE::HTTP_NOT_FOUND);
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

            $post->save();
            $post->refresh()->loadCount('likes');

            // Log post update details
            return response()->json(['message' => 'success', 'data' => $post], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => ['message' => 'Resource not found', 'resource' => 'posts']], RESPONSE::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            Post::where('id', $id)->firstOrFail()->delete();

            // Log post deletion
            return response()->json(['message' => 'success', 'data' => null], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            // Log unknown resource details
            return response()->json(['error' => ['message' => 'Resource not found', 'resource' => 'posts']], RESPONSE::HTTP_BAD_REQUEST);
        }
    }
}