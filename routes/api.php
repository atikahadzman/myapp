<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\RatesController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;

// only allow for admin
Route::middleware(['auth:sanctum', 'is_admin'])->group(function () {
    Route::apiResource('user', UserController::class);
});

// logging purposes
Route::middleware('request.logger')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
    Route::apiResource('books', BookController::class);
    Route::apiResource('progress', ProgressController::class);
    Route::apiResource('rate', RatesController::class);
    Route::apiResource('role', RoleController::class);
    Route::apiResource('user', UserController::class);
});

// guest can access these
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// only authenticated user can access these
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    // Route::get('/user', [AuthController::class, 'user']);
    Route::apiResource('books', BookController::class);
    Route::apiResource('progress', ProgressController::class)->whereNumber('progress');
    Route::get('/progress/by-user', [ProgressController::class, 'getByUserId']);
    Route::apiResource('rate', RatesController::class);
    Route::get('/rate/by-user/{id}', [RatesController::class, 'getSelfRateByBookId']);
    Route::get('/rate/by-book-id/{id}', [RatesController::class, 'getByBookId']);
    Route::apiResource('role', RoleController::class);
    Route::get('/book/show-pdf', [BookController::class, 'showPdf']);
    Route::get('/book/books-progress/{id}', [BookController::class, 'getBooksWithProgress']);
    Route::get('/book/book-of-the-month', [BookController::class, 'getBookOfTheMonth']);
});

Route::post('/tokens/create', function (Request $request) {
    $token = $request->user()->createToken($request->token_name);
 
    return [
        'token' => $token->plainTextToken,
        'expires_at' => config('sanctum.expiration')
            ? now()->addMinutes(config('sanctum.expiration'))
            : null,
    ];
});

Route::get('/pdf/{mediaId}', function ($mediaId) {
    $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::findOrFail($mediaId);
    $path = $media->getPath();

    if (!file_exists($path)) {
        abort(404, 'File not found');
    }

    $allowedOrigins = [
        'http://localhost:5173',
        'http://localhost:5174',
    ];

    return response()->file($path, [
        'Content-Type' => 'application/pdf',
        'Access-Control-Allow-Origin' => $allowedOrigins,
        'Access-Control-Allow-Headers' => 'Authorization, Content-Type',
    ]);
})->middleware('auth:sanctum');

