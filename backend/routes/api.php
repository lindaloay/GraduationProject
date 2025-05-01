<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\BusinessCategoryController;
use App\Http\Controllers\Api\BusinessController as ApiBusiness;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\BusinessController as AdminBusinessController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;
use Illuminate\Http\Request;
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

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/business/image/{path}', [BusinessController::class, 'getImage']);
Route::get('/businesses', [BusinessController::class, 'getAllBusinesses']);
Route::get('/businesses/{id}', [BusinessController::class, 'getBusinessById']);
Route::get('/businesses/{id}/feedbacks', [FeedbackController::class, 'getBusinessFeedbacks']);
Route::get('/businesses/{businessId}/favorites/count', [FavoriteController::class, 'getFavoritesCount']);

// Updated API routes with enhanced search and filtering
Route::get('/v2/businesses', [ApiBusiness::class, 'index']);
Route::get('/v2/businesses/{id}', [ApiBusiness::class, 'show']);
Route::get('/business-categories', [ApiBusiness::class, 'getCategories']);

// Business Categories routes
Route::get('/categories', [BusinessCategoryController::class, 'index']);
Route::get('/categories/{id}', [BusinessCategoryController::class, 'show']);
Route::get('/categories/{id}/businesses', [BusinessCategoryController::class, 'getBusinessesByCategory']);

// Admin routes
Route::prefix('admin')->group(function () {
    // Admin auth routes (public)
    Route::post('/login', [AdminAuthController::class, 'login']);
    
    // Protected admin routes
    Route::middleware([
        'auth:sanctum',
        \App\Http\Middleware\ActiveUserMiddleware::class,
        \App\Http\Middleware\AdminMiddleware::class
    ])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::get('/profile', [AdminAuthController::class, 'profile']);
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);
        
        // Admin Business Category routes
        Route::get('/categories', [AdminCategoryController::class, 'index']);
        Route::post('/categories', [AdminCategoryController::class, 'store']);
        Route::get('/categories/{id}', [AdminCategoryController::class, 'show']);
        Route::put('/categories/{id}', [AdminCategoryController::class, 'update']);
        Route::delete('/categories/{id}', [AdminCategoryController::class, 'destroy']);
        Route::get('/categories-stats', [AdminCategoryController::class, 'stats']);
        
        // Admin Business Management routes
        Route::get('/businesses', [AdminBusinessController::class, 'index']);
        Route::get('/businesses/{id}', [AdminBusinessController::class, 'show']);
        Route::delete('/businesses/{id}', [AdminBusinessController::class, 'destroy']);
        Route::put('/businesses/{id}', [AdminBusinessController::class, 'update']);
        Route::post('/businesses/{id}/update-with-images', [AdminBusinessController::class, 'updateWithImages']);
        
        // Admin Feedback Management routes
        Route::get('/feedbacks', [AdminFeedbackController::class, 'index']);
        Route::get('/feedbacks/{id}', [AdminFeedbackController::class, 'show']);
        Route::delete('/feedbacks/{id}', [AdminFeedbackController::class, 'destroy']);
        Route::get('/feedbacks-stats', [AdminFeedbackController::class, 'stats']);
        
        // Admin User Management routes
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::get('/users/{id}', [AdminUserController::class, 'show']);
        Route::put('/users/{id}', [AdminUserController::class, 'update']);
        Route::patch('/users/{id}/toggle-status', [AdminUserController::class, 'toggleStatus']);
        Route::post('/users/{id}/reset-password', [AdminUserController::class, 'resetPassword']);
    });
});

// Protected routes
Route::middleware([
    'auth:sanctum',
    \App\Http\Middleware\ActiveUserMiddleware::class
])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // User routes
    Route::get('/user/profile', [UserController::class, 'getProfile']);
    Route::post('/user/update', [UserController::class, 'updateProfile']);

    // Business routes
    Route::post('/business/details', [BusinessController::class, 'storeDetails']);
    Route::get('/business/details', [BusinessController::class, 'getDetails']);
    Route::post('/business/additional-details', [BusinessController::class, 'storeAdditionalDetails']);
    Route::post('/business/upload-image', [BusinessController::class, 'uploadImage']);
    Route::post('/business/upload-images', [BusinessController::class, 'uploadImages']);
    Route::post('/business/delete-gallery-image', [BusinessController::class, 'deleteGalleryImage']);

    // Feedback routes
    Route::post('/feedback', [FeedbackController::class, 'store']);
    Route::post('/feedbacks', [FeedbackController::class, 'store']);
    
    // Favorite routes
    Route::post('/favorites', [FavoriteController::class, 'addToFavorites']);
    Route::delete('/favorites', [FavoriteController::class, 'removeFromFavorites']);
    Route::get('/favorites', [FavoriteController::class, 'getUserFavorites']);
    Route::get('/favorites/check/{businessId}', [FavoriteController::class, 'checkFavorite']);
});