<?php

use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\HotspotController;
use App\Http\Controllers\Api\Admin\LeadController;
use App\Http\Controllers\Api\Admin\MediaController;
use App\Http\Controllers\Api\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Api\Admin\SceneController;
use App\Http\Controllers\Api\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Api\Admin\SettingController;
use App\Http\Controllers\Api\Admin\TestimonialController;
use App\Http\Controllers\Api\Admin\TestimonialInvitationController as AdminTestimonialInvitationController;
use App\Http\Controllers\Api\Admin\TourController as AdminTourController;
use App\Http\Controllers\Api\Website\ContactController;
use App\Http\Controllers\Api\Website\GalleryController;
use App\Http\Controllers\Api\Website\HomeController;
use App\Http\Controllers\Api\Website\MediaFileController;
use App\Http\Controllers\Api\Website\ProjectController;
use App\Http\Controllers\Api\Website\ServiceController;
use App\Http\Controllers\Api\Website\TestimonialInvitationController as PublicTestimonialInvitationController;
use App\Http\Controllers\Api\Website\TourController;
use Illuminate\Support\Facades\Route;

Route::prefix('public')->group(function () {
    Route::get('home', HomeController::class);
    Route::get('media/{media:uuid}', MediaFileController::class);
    Route::get('gallery', GalleryController::class);
    Route::get('projects', [ProjectController::class, 'index']);
    Route::get('projects/{slug}', [ProjectController::class, 'show']);
    Route::get('projects/{slug}/tour', [ProjectController::class, 'tour']);
    Route::get('services', [ServiceController::class, 'index']);
    Route::get('services/{slug}', [ServiceController::class, 'show']);
    Route::get('tours/{slug}', [TourController::class, 'show']);
    Route::get('testimonial-invitations/{token}', [PublicTestimonialInvitationController::class, 'show']);
    Route::post('testimonial-invitations/{token}', [PublicTestimonialInvitationController::class, 'submit']);
    Route::post('contact', [ContactController::class, 'store']);
    Route::post('leads', [ContactController::class, 'store']);
});

Route::prefix('admin')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::get('dashboard', DashboardController::class);

        Route::apiResource('projects', AdminProjectController::class);
        Route::post('projects/{project}/publish', [AdminProjectController::class, 'publish']);
        Route::post('projects/{project}/archive', [AdminProjectController::class, 'archive']);

        Route::get('media', [MediaController::class, 'index']);
        Route::post('media', [MediaController::class, 'store']);
        Route::put('media/{medium}', [MediaController::class, 'update']);
        Route::post('media/reorder', [MediaController::class, 'reorder']);
        Route::delete('media/{medium}', [MediaController::class, 'destroy']);

        Route::apiResource('tours', AdminTourController::class);
        Route::post('tours/{tour}/publish', [AdminTourController::class, 'publish']);

        Route::post('tours/{tour}/scenes', [SceneController::class, 'store']);
        Route::put('scenes/{scene}', [SceneController::class, 'update']);
        Route::delete('scenes/{scene}', [SceneController::class, 'destroy']);

        Route::post('scenes/{scene}/hotspots', [HotspotController::class, 'store']);
        Route::put('hotspots/{hotspot}', [HotspotController::class, 'update']);
        Route::delete('hotspots/{hotspot}', [HotspotController::class, 'destroy']);

        Route::apiResource('leads', LeadController::class)->only(['index', 'show', 'update', 'destroy']);
        Route::apiResource('services', AdminServiceController::class);
        Route::apiResource('testimonials', TestimonialController::class);
        Route::get('testimonial-invitations', [AdminTestimonialInvitationController::class, 'index']);
        Route::post('testimonial-invitations', [AdminTestimonialInvitationController::class, 'store']);
        Route::post('testimonial-invitations/{invitation}/resend', [AdminTestimonialInvitationController::class, 'resend']);
        Route::delete('testimonial-invitations/{invitation}', [AdminTestimonialInvitationController::class, 'destroy']);

        Route::get('settings', [SettingController::class, 'index']);
        Route::post('settings', [SettingController::class, 'upsert']);
        Route::delete('settings/{setting}', [SettingController::class, 'destroy']);
    });
});
