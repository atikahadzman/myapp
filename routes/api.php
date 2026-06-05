<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\RatesController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::apiResource('books', BookController::class);
    Route::get('/progress/by-user', [ProgressController::class, 'getByUserId']);
    Route::apiResource('progress', ProgressController::class);
    Route::apiResource('rate', RatesController::class);
    Route::get('/rate/by-book-id/{id}', [RatesController::class, 'getByBookId']);
    Route::apiResource('role', RoleController::class);
    Route::apiResource('user', UserController::class);
    Route::get('/book/show-pdf', [BookController::class, 'showPdf']);
    Route::get('/book/books-progress/{id}', [BookController::class, 'getBooksWithProgress']);
});

Route::post('/tokens/create', function (Request $request) {
    $token = $request->user()->createToken($request->token_name);
 
    return ['token' => $token->plainTextToken];
});

Route::get('/pdf/{mediaId}', function ($mediaId) {
    $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::findOrFail($mediaId);
    $path = $media->getPath();

    if (!file_exists($path)) {
        abort(404, 'File not found');
    }

    return response()->file($path, [
        'Content-Type' => 'application/pdf',
        'Access-Control-Allow-Origin' => 'http://localhost:5173',
        'Access-Control-Allow-Headers' => 'Authorization, Content-Type',
    ]);
})->middleware('auth:sanctum');

