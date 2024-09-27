<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Log;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $blogs = Blog::all();

        return response()->json(['blogs' => $blogs], Response::HTTP_OK);
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
        $blog = Blog::create($validated);
        // Log blog creation details

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
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Resource not found'], RESPONSE::HTTP_NOT_FOUND);
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
            // Log blog creation details
            Blog::where('id', $id)->firstOrFail()->update($validated);

            $blog = Blog::find($id);

            return response()->json(['message' => 'success', 'data' => $blog], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Resource not found'], RESPONSE::HTTP_NOT_FOUND);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Log blog creation details
            Blog::where('id', $id)->firstOrFail()->delete();

            return response()->json(['message' => 'success', 'data' => null], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Resource not found'], RESPONSE::HTTP_NOT_FOUND);
        }
    }
}