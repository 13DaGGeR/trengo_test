<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('categories', [\App\Http\Controllers\CategoryController::class, 'index']);
Route::get('articles/{id}', [\App\Http\Controllers\ArticleController::class, 'show']);
Route::middleware(['throttle:create_articles'])->group(static function () {
    Route::post('articles', [\App\Http\Controllers\ArticleController::class, 'store']);
});
Route::middleware(['throttle:rate_articles'])->group(static function () {
    Route::post('ratings', [\App\Http\Controllers\RatingController::class, 'store']);
});
Route::get('articles', [\App\Http\Controllers\ArticleController::class, 'index']);
