<?php

use App\Http\Controllers\Api\AppConfigController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ExperienceController;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\MapController;
use App\Http\Controllers\Api\MayaConversationController;
use App\Http\Controllers\Api\MayaMessageController;
use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\Api\PointOfInterestController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile app API
|--------------------------------------------------------------------------
|
| Every endpoint the Smartsense app calls. Reading is open, so a traveller can
| look around before signing up; everything that writes needs the Sanctum
| token that signing in returns. Answers follow the app's language, sent as
| Accept-Language.
|
*/

Route::post('register', [AuthController::class, 'register'])->middleware('throttle:auth-register');
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:auth-login');
Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:auth-password');

Route::get('config', AppConfigController::class);
Route::get('categories', [CategoryController::class, 'index']);
Route::get('map', MapController::class);

Route::get('points-of-interest', [PointOfInterestController::class, 'index']);
Route::get('points-of-interest/{pointOfInterest}', [PointOfInterestController::class, 'show']);
Route::get('points-of-interest/{pointOfInterest}/reviews', [ReviewController::class, 'forPointOfInterest']);

Route::get('experiences', [ExperienceController::class, 'index']);
Route::get('experiences/{experience}', [ExperienceController::class, 'show']);
Route::get('experiences/{experience}/reviews', [ReviewController::class, 'forExperience']);

Route::get('posts', [PostController::class, 'index']);
Route::get('posts/{post}', [PostController::class, 'show']);
Route::get('posts/{post}/comments', [CommentController::class, 'index']);

Route::get('users/{user}', [UserController::class, 'show']);
Route::get('users/{user}/posts', [UserController::class, 'posts']);
Route::get('users/{user}/activity', [UserController::class, 'activity']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('me', [MeController::class, 'show']);
    Route::put('me', [MeController::class, 'update']);
    Route::put('me/password', [MeController::class, 'updatePassword']);
    Route::post('me/avatar', [MeController::class, 'updateAvatar']);
    Route::delete('me/avatar', [MeController::class, 'destroyAvatar']);
    Route::delete('me', [MeController::class, 'destroy']);

    Route::middleware('throttle:community')->group(function () {
        Route::post('points-of-interest/{pointOfInterest}/like', [LikeController::class, 'pointOfInterest']);
        Route::post('points-of-interest/{pointOfInterest}/reviews', [ReviewController::class, 'storeForPointOfInterest']);
        Route::post('experiences/{experience}/reviews', [ReviewController::class, 'storeForExperience']);
        Route::delete('reviews/{review}', [ReviewController::class, 'destroy']);

        Route::post('posts', [PostController::class, 'store']);
        Route::delete('posts/{post}', [PostController::class, 'destroy']);
        Route::post('posts/{post}/like', [LikeController::class, 'post']);
        Route::post('posts/{post}/comments', [CommentController::class, 'store']);
        Route::delete('comments/{comment}', [CommentController::class, 'destroy']);

        Route::post('users/{user}/follow', [FollowController::class, 'store']);
    });

    Route::get('maya/conversations', [MayaConversationController::class, 'index']);
    Route::post('maya/conversations', [MayaConversationController::class, 'store'])->middleware('throttle:maya');
    Route::get('maya/conversations/{conversation}', [MayaConversationController::class, 'show']);
    Route::delete('maya/conversations/{conversation}', [MayaConversationController::class, 'destroy']);
    Route::post('maya/conversations/{conversation}/messages', [MayaMessageController::class, 'store'])->middleware('throttle:maya');
});
