<?php

use App\Http\Controllers\api\v1\ApiAuthController;
use App\Http\Controllers\api\v1\CountriesController;
use App\Http\Controllers\api\v1\FAQController;
use App\Http\Controllers\api\v1\NotificationController;
use App\Http\Controllers\api\v1\PatientController;
use App\Http\Controllers\CategoryServicesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('checkEmail', [ApiAuthController::class, 'checkEmail']);

Route::controller(ApiAuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('signup', 'signup')->middleware('throttle:signup_limit');
    Route::post('send-otp', 'sendOtpVerification')->middleware('throttle:otp_limit');
    Route::post('verify-otp', 'verifyOtp');    
    Route::post('forgot-password', 'forgotPassword')->middleware('throttle:forgot_limit'); 
    Route::get('/reset-password','showResetForm')->name('password.reset');     
    Route::post('/reset-password', 'reset')->name('password.update');  
});

Route::get('user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')
    ->group(function () {
        Route::post('login', [ApiAuthController::class, 'login'])->name('login');
    });

Route::middleware(['throttle:api_global','auth:sanctum'])
    ->group(function () {
        Route::prefix('auth')
            ->controller(ApiAuthController::class)
            ->group(function () {
                Route::get('profile', 'profile');
                Route::post('updateProfile', 'updateProfile');
                Route::post('/change-password', 'changePassword');
                Route::post('/logout', 'logout');
                Route::delete('/delete-user', 'deleteUser');
              
            });
    });

Route::middleware(['throttle:api_global','auth:sanctum'])
    ->group(function () {
        Route::prefix('auth')
            ->controller(FAQController::class)
            ->group(function () {
                Route::get('show-faqs', 'showFAQ');
            });
    });


Route::middleware(['throttle:api_global','auth:sanctum'])
    ->group(function () {
        Route::prefix('auth')
            ->controller(CategoryServicesController::class)
            ->group(function () {
                Route::get('show-category-services', 'showCategoryServices');
            });
    });
