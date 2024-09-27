<?php

use App\Http\Controllers\BlogController;
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
        Route::apiResources([
            'blogs' => BlogController::class,
        ]);

        Route::get('/test', function () {
            return "Hello world";
        });
    });
});