<?php

use App\Http\Controllers\api\ApiAuthController;
use App\Http\Controllers\api\CountriesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\api\Child\ChildAuthController;
use App\Http\Controllers\api\Child\ChildAuthController;
use App\Http\Controllers\api\Parent\ParentAuthController;
use App\Http\Controllers\api\Business\BusinessAuthController;
use App\Http\Controllers\api\v1\GeneralApiController;
use Symfony\Component\HttpFoundation\Response;

// Api Routes
Route::prefix('v1')
    //->middleware(['ApiAuthMiddleware:admin']) // Middleware after prefix
    ->group(function () {

        Route::get('/get-countries', [CountriesController::class, 'getActiveCountries']); // Parent signup
        Route::post('/checkEmail', [ApiAuthController::class, 'checkEmail']);
        Route::post('/get-hobbies', [GeneralApiController::class, 'getHobbies']);
        Route::post('/get-interests', [GeneralApiController::class, 'getInterests']);
        Route::get('/get-levels', [GeneralApiController::class, 'getLevels']);

        Route::controller(ApiAuthController::class)->group(function () {

            Route::post('/signup', 'signup');
            Route::post('/send-otp', 'sendOtpVerification')->middleware('throttle:otp_limit');
            Route::post('/verify-otp', 'verifyOtp');
            Route::post('/forgot-password', 'forgotPassword')->middleware('throttle:otp_limit');
            Route::post('/forgot-otp-verify', 'forgotPasswordVerifyOtp');
            Route::post('/reset-password', 'resetPassword');
        });

        // AUTH ROUTES
        Route::get('/user', function (Request $request) {
            return $request->user();
        })->middleware('auth:sanctum');

        // Public Routes - No authentication required
        Route::prefix('auth')->group(function () {

            // User login route (No authentication required)
            Route::post('/login', [ApiAuthController::class, 'login']);

            // User signup routes - these routes do not require authentication
            Route::prefix('child')->group(function () {
                Route::post('/signup', [ChildAuthController::class, 'register']); // Child signup
            });

            Route::prefix('parent')->group(function () {
                Route::post('/signup', [ParentAuthController::class, 'register']); // Parent signup
            });

            Route::prefix('business')->group(function () {
                Route::post('/signup', [BusinessAuthController::class, 'register']); // Business signup
            });
        });

        // Protected Routes - Authentication required (Requires 'auth:sanctum' middleware)
        Route::middleware(['auth:sanctum'])->group(function () {

            // Group all routes under the 'auth' prefix for authenticated users
            Route::prefix('auth')->group(function () {

                // Logout route (Authenticated users only)
                Route::controller(ApiAuthController::class)->group(function () {
                    Route::post('/logout', 'logout');
                    Route::post('/change-password', 'changePassword');
                    Route::delete('delete', [ApiAuthController::class, 'destroy']);
                });

                // Child-related routes (Protected, requires authentication)
                Route::prefix('child')->group(function () {
                    Route::get('/profile', [ChildAuthController::class, 'getProfile']);  // Child profile
                    Route::post('/update-profile', [ChildAuthController::class, 'updateChildProfile']);

                });

                // Parent-related routes (Protected, requires authentication)
                Route::prefix('parent')->group(function () {
                    Route::get('/profile', [ParentAuthController::class, 'getProfile']);  // Parent profile
                    Route::get('/ChildrenList', [ParentAuthController::class, 'getChildrenList']);  // Parent getChildrenList
                    Route::post('/approveRejectChild', [ParentAuthController::class, 'approveRejectChildRequest']);  // Parent approveRejectChild
                    Route::post('/update-profile', [ParentAuthController::class, 'updateParentProfile']);
                });

                // Business-related routes (Protected, requires authentication)
                Route::prefix('business')->group(function () {
                    Route::get('/profile', [BusinessAuthController::class, 'getProfile']);  // Business profile
                });
            });
        });
    });

Route::fallback(function (Request $request) {
    if ($request->expectsJson() || $request->is('api/*')) { // Important check
        return response()->json(['error' => 'Route Not Found.'], 404);
    }

    // Handle non-API requests (if any) - perhaps redirect
    // return redirect('/404'); // Or whatever is appropriate for your application
});
