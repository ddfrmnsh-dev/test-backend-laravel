<?php

use App\Http\Controllers\AuthenticateController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureEmailVerified;
use App\Http\Middleware\EnsureSanctumTokenIsValid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route::middleware([EnsureSanctumTokenIsValid::class, EnsureEmailVerified::class])->group(function () {
Route::middleware([EnsureSanctumTokenIsValid::class])->group(function () {
    Route::get('/user/{id}', [UserController::class, 'getUserById']);
    Route::get('/users', [UserController::class, 'getAllUser']);
    Route::post('/user', [UserController::class, 'createUser']);
    Route::put('/user/{id}', [UserController::class, 'updateUser']);
    Route::delete('/user/{id}', [UserController::class, 'deleteUser']);

    Route::get('/categories/{id}', [CategoriesController::class, 'getCategoryById']);
    Route::get('/categories', [CategoriesController::class, 'getAllCategory']);
    Route::post('/categories', [CategoriesController::class, 'createCategory']);
    Route::put('/categories/{id}', [CategoriesController::class, 'updateCategory']);
    Route::delete('/categories/{id}', [CategoriesController::class, 'deleteCategory']);

    Route::get('/post/{id}', [PostController::class, 'getPostById']);
    Route::get('/posts', [PostController::class, 'getAllPost']);
    Route::post('/posts', [PostController::class, 'createPost']);
    Route::put('/post/{id}', [PostController::class, 'updatePost']);
    Route::delete('/post/{id}', [PostController::class, 'deletePost']);

    Route::post('/bookmark', [BookmarkController::class, 'addBookmark']);
    Route::delete('/bookmark/{postId}', [BookmarkController::class, 'deleteBookmark']);
    Route::get('/bookmarks', [BookmarkController::class, 'getBookmarkByUser']);
});

route::post('/forget-password', [AuthenticateController::class, 'sendResetLinkEmail']);
route::post('/reset-password', [AuthenticateController::class, 'resetPassword'])->name('password.reset');

Route::post('/signin', [AuthenticateController::class, 'login']);
Route::post('/signout', [AuthenticateController::class, 'logout'])->middleware('auth:sanctum');

Route::post('/register', [AuthenticateController::class, 'registerUser']);

Route::get('/email/verify/{id}/{hash}', [AuthenticateController::class, 'verifyUser'])->name('verification.verify');
Route::post('/email/resend', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return response()->json(['message' => 'Verification link sent.']);
})->middleware([EnsureSanctumTokenIsValid::class]);
