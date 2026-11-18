<?php

use App\Http\Controllers\Admin\Community\CommentController as CommunityCommentController;
use App\Http\Controllers\Admin\Community\PostController as CommunityPostController;
use App\Http\Controllers\Admin\Community\ReviewController as CommunityReviewController;
use App\Http\Controllers\Admin\DestinationController;
use App\Http\Controllers\Admin\ExperienceController;
use App\Http\Controllers\Admin\LegalDocumentController;
use App\Http\Controllers\Admin\LookupController;
use App\Http\Controllers\Admin\Lookups\PointOfInterestCategoryController;
use App\Http\Controllers\Admin\Lookups\TopicController;
use App\Http\Controllers\Admin\MayaSettingsController;
use App\Http\Controllers\Admin\PointOfInterestController;
use App\Http\Controllers\Admin\PointOfInterestPhotoController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LegalPageController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/*
|--------------------------------------------------------------------------
| The email verification link
|--------------------------------------------------------------------------
|
| Outside the locale prefix on purpose: the link is signed and the signature
| covers the path, so a redirect into a locale prefix would invalidate it.
|
*/

Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::group(
    [
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath'],
    ], function () {
        /*
        |----------------------------------------------------------------------
        | Public site
        |----------------------------------------------------------------------
        */

        Route::inertia('/', 'welcome')->name('home');

        // Linked from the app's sign-in and sign-up screens, so each keeps a fixed address.
        Route::get('/terms', [LegalPageController::class, 'terms'])->name('terms');
        Route::get('/privacy', [LegalPageController::class, 'privacy'])->name('privacy');
        Route::get('/cookies', [LegalPageController::class, 'cookies'])->name('cookies');
        Route::get('/ai', [LegalPageController::class, 'ai'])->name('ai');

        /*
        |----------------------------------------------------------------------
        | Admin panel
        |----------------------------------------------------------------------
        |
        | Staff only. Each area is gated by its section permission, and every
        | record is still authorised by its own policy.
        |
        */

        Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
            Route::get('dashboard', DashboardController::class)->name('dashboard');

            Route::name('admin.')->group(function () {
                Route::middleware('permission:content.view')->group(function () {
                    Route::resource('points-of-interest', PointOfInterestController::class)
                        ->except('show')
                        ->parameters(['points-of-interest' => 'pointOfInterest']);
                    Route::post('points-of-interest/{pointOfInterest}/photos', [PointOfInterestPhotoController::class, 'store'])
                        ->name('points-of-interest.photos.store');
                    Route::post('points-of-interest/{pointOfInterest}/photos/{media}/cover', [PointOfInterestPhotoController::class, 'cover'])
                        ->name('points-of-interest.photos.cover');
                    Route::delete('points-of-interest/{pointOfInterest}/photos/{media}', [PointOfInterestPhotoController::class, 'destroy'])
                        ->name('points-of-interest.photos.destroy');

                    Route::resource('experiences', ExperienceController::class)->except('show');
                    Route::post('experiences/{experience}/measure', [ExperienceController::class, 'measure'])
                        ->name('experiences.measure');

                    Route::resource('destinations', DestinationController::class)->except('show');

                    Route::prefix('lookups')->name('lookups.')->group(function () {
                        Route::get('/', [LookupController::class, 'index'])->name('index');
                        Route::resource('categories', PointOfInterestCategoryController::class)
                            ->parameters(['categories' => 'category']);
                        Route::resource('topics', TopicController::class)
                            ->parameters(['topics' => 'topic']);
                    });
                });

                Route::middleware('permission:community.view')->prefix('community')->name('community.')->group(function () {
                    Route::get('posts', [CommunityPostController::class, 'index'])->name('posts.index');
                    Route::patch('posts/{post}', [CommunityPostController::class, 'update'])->name('posts.update');
                    Route::delete('posts/{post}', [CommunityPostController::class, 'destroy'])->name('posts.destroy');
                    Route::get('comments', [CommunityCommentController::class, 'index'])->name('comments.index');
                    Route::delete('comments/{comment}', [CommunityCommentController::class, 'destroy'])->name('comments.destroy');
                    Route::get('reviews', [CommunityReviewController::class, 'index'])->name('reviews.index');
                    Route::delete('reviews/{review}', [CommunityReviewController::class, 'destroy'])->name('reviews.destroy');
                });

                Route::resource('users', UserController::class);

                Route::resource('roles', RoleController::class)
                    ->only(['index', 'store', 'update', 'destroy']);

                Route::middleware('permission:settings.view')->group(function () {
                    Route::get('maya', [MayaSettingsController::class, 'edit'])->name('maya.edit');
                    Route::put('maya', [MayaSettingsController::class, 'update'])->name('maya.update');

                    // The set of legal documents is fixed by the public routes that show them.
                    Route::resource('legal-documents', LegalDocumentController::class)
                        ->only(['index', 'edit', 'update'])
                        ->parameters(['legal-documents' => 'legalDocument']);
                });
            });
        });

        require __DIR__.'/auth.php';
        require __DIR__.'/settings.php';
    }
);
