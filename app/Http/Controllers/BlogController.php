<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $blogs = Blog::all();

        return response()->json(['message' => 'success', 'data' => ['blogs' => $blogs]], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Log::channel('stderr')->info('Request input', ['input' => $input]);
        $validated = $request->validate([
            'title' => ['required', 'string'],
            'description' => ['string', 'nullable'],
        ]);

        $user_id = Auth::id();
        $validated['user_id'] = $user_id;

        Log::info('Creating new Blog: {data}', ['data' => $validated]);

        $blog = Blog::create($validated);

        $ctx = [
            'user_id' => $user_id,
            'data' => $blog
        ];

        $this->log_handler('Created new Blog', $ctx);

        return response()->json(['message' => 'success', 'data' => $blog], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // retrieve record
        try {
            $blog = Blog::with('posts')->where('id', $id)->firstOrFail();
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
            'description' => ['string', 'nullable'],
        ]);

        try {
            $blog = Blog::where('id', $id)->firstOrFail();

            $ctx = [
                'user_id' => Auth::id(),
                'data' => $blog
            ];

            $this->log_handler('Updating Blog', $ctx);

            $blog->update($validated);

            return response()->json(['message' => 'success', 'data' => $blog], Response::HTTP_OK);
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
            $blog = Blog::where('id', $id)->firstOrFail();

            $ctx = ['user_id' => Auth::id(), 'data' => $blog];

            $this->log_handler('Deleting Blog', $ctx);

            $blog->delete();

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
                'resource_type' => 'Blog',
                'blog_id' => $id,
                'timestamps' => now()
            ];
            $this->log_handler('Unknown resource', $ctx);
            return response()->json(['error' => ['message' => "Resource with id '{$id}' not found", 'resource' => 'blog']], RESPONSE::HTTP_BAD_REQUEST);
        } else {
            $ctx = ['err' => $e->getMessage()];
            Log::debug('Uncaught Exception: {ctx}', ['ctx' => $ctx]);
            throw $e;
        }
    }

    private function log_handler(string $message, mixed $ctx)
    {
        Log::info($message . ': {ctx}', ['ctx' => $ctx]);
    }
}
