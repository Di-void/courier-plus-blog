<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostLikeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    // Protected Routes
    Route::middleware(['auth:sanctum', 'courier.custom'])->group(function () {
        Route::post('/posts/{id}/like', [PostLikeController::class, 'store']);
        Route::post('/posts/{id}/comment', [CommentController::class, 'store']);

        Route::apiResources([
            'blogs' => BlogController::class,
            'posts' => PostController::class,
        ]);
    });
});