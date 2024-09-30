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
        $validated = $request->validate([
            'title' => ['required', 'string'],
            'description' => ['string', 'nullable'],
        ]);

        $user_id = Auth::id();
        $validated['user_id'] = $user_id;

        Log::info('Creating new Blog', ['data' => $validated]);

        $blog = Blog::create($validated);

        $ctx = [
            'user_id' => $user_id,
            'blog' => $blog
        ];

        Log::info('Created New Blog', ['data' => $ctx]);

        return response()->json(['message' => 'success', 'data' => $blog], Response::HTTP_CREATED);
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
            return $this->exception_handler($e, $id, 'Read_Blog');
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
                'blog' => $blog
            ];

            Log::info('Updating Blog', ['data' => $ctx]);

            $blog->update($validated);

            return response()->json(['message' => 'success', 'data' => $blog], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->exception_handler($e, $id, 'Update_Blog');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $blog = Blog::where('id', $id)->firstOrFail();

            $ctx = ['user_id' => Auth::id(), 'blog' => $blog];

            Log::info('Deleting Blog', ['data' => $ctx]);

            $blog->delete();

            return response()->json(['message' => 'success', 'data' => null], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->exception_handler($e, $id, 'Delete_Blog');
        }
    }

    private function exception_handler(\Exception $e, int $id, string $action)
    {
        if ($e instanceof ModelNotFoundException) {
            $ctx = [
                'user_id' => Auth::id(),
                'resource_type' => 'Blog',
                'blog_id' => $id,
                'action' => $action
            ];

            Log::info('Unknown resource', ['data' => $ctx]);
            return response()->json(['message' => 'error', 'error' => ['message' => "Resource with id '{$id}' not found", 'resource' => 'blog']], RESPONSE::HTTP_BAD_REQUEST);
        } else {
            $ctx = ['err' => $e->getMessage(), 'resource_type' => 'Blog', 'action' => $action];
            Log::debug('Uncaught Exception', ['data' => $ctx]);
            throw $e;
        }
    }
}