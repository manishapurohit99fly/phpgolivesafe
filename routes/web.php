<?php

use App\Http\Controllers\admin\AdminAuthController;
use App\Http\Controllers\admin\AdminUserController;
use App\Http\Controllers\admin\AdminDashboardController;
use App\Http\Controllers\admin\AdminSiteSettingController;
use App\Http\Controllers\admin\AdminProjectController;
use App\Http\Controllers\admin\AdminTechStackController;
use App\Http\Controllers\admin\AdminAssessmentController;
use App\Http\Controllers\admin\AdminProjectReportController;
use App\Http\Controllers\admin\AdminAssessmentListReportController;
use App\Http\Controllers\user\UserProjectController;
use App\Http\Controllers\user\UserAssessmentController;
use App\Http\Controllers\PublicReportController;

use App\Mail\ForgotPasswordMail;
use App\Services\SiteSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/privacy-policy', function () {
    return view('privacy_policy');
});

Route::get('/terms&condition', function () {
    return view('terms_condition');
});

Route::get('/email', function () {
    $otp = '123456';
    $name = 'test';
    return new ForgotPasswordMail($otp, $name);
});

Route::get('/clear-cache', function () {
    Artisan::call('optimize:clear');
    return response()->json(['message' => 'Application cache cleared']);
});

Route::fallback(function (Request $request) {
    if ($request->expectsJson() || $request->is('api/*')) {
        return response()->json(['error' => 'User Not Found.'], 404);
    }
});

// ---------------------------------- ADMIN AUTH (guest) ----------------------------------------

Route::name('admin.')->middleware(['guest:admin', 'prevent-back-history'])->group(function () {

    Route::view('/login', 'admin.auth.login')->name('login')->middleware('prevent-back-history');
    Route::view('/forgot-password', 'admin.auth.forgot-password')->name('forgotPassword');

    Route::controller(AdminAuthController::class)->group(function () {
        Route::post('/login', 'loginAuth')->name('loginAuth');
        Route::get('/otp-verify', 'otpForm')->name('otpForm');
        Route::post('/otp-verify', 'verifyOtp')->name('verifyOtp');
        Route::post('/resend-otp', 'resendOtp')->name('resendOtp');

        Route::post('/forgot-password', 'sendResetToken')->name('sendResetToken');
        Route::post('/password/reset', 'passwordReset')->name('passwordReset');
        Route::get('/password/reset/{token}', 'showResetForm')->name('showResetForm');
        Route::post('/password/reset/{token}', 'resetPassword')->name('resetPassword');
    });
});

// ---------------------------------- ADMIN AUTHENTICATED -------------------------------------------

Route::prefix('admin')->name('admin.')->middleware(['auth:admin', 'user.active'])->group(function () {

    // Common routes — accessible by any authenticated role
    Route::get('logout', [AdminAuthController::class, 'logout'])->name('logout');
    Route::view('/profile', 'admin.profile.index')->name('getProfile');
    Route::post('/update-profile', [AdminDashboardController::class, 'updateProfile'])->name('updateProfile');
    Route::post('update-status', [AdminDashboardController::class, 'updateStatus'])->name('updateStatus');
    Route::view('/change-password', 'admin.change-password.index')->name('changePassword.view');
    Route::post('/change-password', [AdminDashboardController::class, 'changePassword'])->name('changePassword.update');

    // ── Admin-only routes (role 1) ──────────────────────────────────────────

    Route::middleware(['role:1'])->group(function () {

        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard/data', [AdminDashboardController::class, 'dashboardData'])->name('dashboard.data');

        Route::prefix('users')->name('users.')->controller(AdminUserController::class)->group(function () {
            Route::get('index', 'index')->name('userIndex');
            Route::get('ajax', 'datatable')->name('datatable');
            Route::get('export', 'exportCsv')->name('userExport');
            Route::get('add', 'userAdd')->name('userAdd');
            Route::post('add', 'userSave')->name('userSave');
            Route::get('edit/{userId}', 'userEdit')->name('userEdit');
            Route::post('update/{userId}', 'userUpdate')->name('userUpdate');
            Route::post('admin/user/destroy', 'userDestroy')->name('userDestroy');
            Route::post('admin/check-email', 'checkEmail')->name('checkEmail');
            Route::post('admin/check-phone', 'checkPhone')->name('checkPhone');
            Route::post('admin/user/reset-password', 'userResetPassword')->name('userResetPassword');
        });

        Route::prefix('site-setting')->name('siteSetting.')->controller(AdminSiteSettingController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::put('/', 'update')->name('update');
        });
     

        // Tech Stack Master
        Route::prefix('tech-stack')->name('tech-stack.')->controller(AdminTechStackController::class)->group(function () {
            Route::get('/',          'index')->name('index');
            Route::get('/create',    'create')->name('create');
            Route::post('/store',    'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::post('/{id}/update', 'update')->name('update');
            Route::post('/destroy',  'destroy')->name('destroy');
        });

        // Projects — CRUD + legacy checklist/verify/report/share (kept for backward compatibility)
        Route::prefix('projects')->name('project.')->controller(AdminProjectController::class)->group(function () {
            Route::get('/',               'index')->name('index');
            Route::get('/list-ajax',      'projectList')->name('list.ajax');
            Route::get('/{id}/users',     'projectUsers')->name('users');
            Route::get('/ajax',           'datatable')->name('datatable');
            Route::get('/add',            'create')->name('create');
            Route::post('/add',           'store')->name('store');
            Route::get('/view/{id}',      'show')->name('show');
            Route::get('/edit/{id}',      'edit')->name('edit');
            Route::post('/update/{id}',   'update')->name('update');
            Route::post('/destroy',       'destroy')->name('destroy');
            Route::get('/checklist/{id}', 'checklist')->name('checklist');
            Route::post('/checklist/{id}','saveChecklist')->name('checklist.save');
            Route::get('/verify/{id}',    'verify')->name('verify');
            Route::post('/verify/{id}',   'saveVerification')->name('verify.save');
            Route::get('/report/{id}',    'report')->name('report');
            Route::get('/report-data/{id}','reportData')->name('report.data');
            Route::post('/share',         'share')->name('share');
        });

        // Assessments — CRUD + checklist + verify + report + share
        Route::prefix('assessments')->name('assessment.')->controller(AdminAssessmentController::class)->group(function () {
            Route::get('/for-project/{projectId}', 'forProject')->name('for-project');
            Route::post('/',                        'store')->name('store');
            Route::get('/edit/{id}',                'edit')->name('edit');
            Route::post('/update/{id}',             'update')->name('update');
            Route::post('/destroy',                 'destroy')->name('destroy');
            Route::get('/checklist/{id}',           'checklist')->name('checklist');
            Route::post('/checklist/{id}',          'saveChecklist')->name('checklist.save');
            Route::get('/verify/{id}',              'verify')->name('verify');
            Route::post('/verify/{id}',             'saveVerification')->name('verify.save');
            Route::get('/report/{id}',              'report')->name('report');
            Route::get('/report-data/{id}',         'reportData')->name('report.data');
            Route::post('/share',                   'share')->name('share');
        });

        // Admin Project Reports dashboard
        Route::prefix('project-reports')->name('project-reports.')->controller(AdminProjectReportController::class)->group(function () {
            Route::get('/',                  'index')->name('index');
            Route::get('/load',              'load')->name('load');
            Route::get('/search',            'search')->name('search');
            Route::get('/assessments',       'assessments')->name('assessments');
            Route::get('/assessment-load',   'assessmentLoad')->name('assessment.load');
        });

        // Assessment List Report
        Route::prefix('reports')->name('reports.')->controller(AdminAssessmentListReportController::class)->group(function () {
            Route::get('/assessment-list',      'index')->name('assessment-list');
            Route::get('/assessment-list/ajax', 'ajax')->name('assessment-list.ajax');
        });


    }); // end role:1


});
Route::prefix('user')->name('user.')->middleware(['auth:admin', 'user.active', 'role:2'])->group(function () {
    
    Route::get('logout', [AdminAuthController::class, 'logout'])->name('logout');
    Route::view('/profile', 'admin.profile.index')->name('getProfile');
    Route::post('/update-profile', [AdminDashboardController::class, 'updateProfile'])->name('updateProfile');
    Route::post('update-status', [AdminDashboardController::class, 'updateStatus'])->name('updateStatus');
    Route::view('/change-password', 'admin.change-password.index')->name('changePassword.view');
    Route::post('/change-password', [AdminDashboardController::class, 'changePassword'])->name('changePassword.update');

    Route::get('dashboard', [UserProjectController::class, 'dashboard'])->name('dashboard');

    Route::prefix('user-projects')->name('project.')->controller(UserProjectController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/list-ajax', 'projectList')->name('list.ajax');
        Route::get('/ajax', 'datatable')->name('datatable');
        Route::get('/verify/{id}', 'verify')->name('verify');
        Route::post('/verify/{id}', 'saveVerification')->name('verify.save');
    });
    
    Route::get('/report/{id}', [AdminProjectController::class, 'report'])->name('project.report');

    Route::get('/user-reports',                    [UserProjectController::class, 'reportIndex'])->name('reports.index');
    Route::get('/user-reports/search',             [UserProjectController::class, 'reportSearch'])->name('reports.search');
    Route::get('/user-reports/load',               [UserProjectController::class, 'reportLoad'])->name('reports.load');
    Route::get('/user-reports/assessment-options', [UserProjectController::class, 'reportAssessments'])->name('reports.assessment.options');
    Route::get('/user-reports/assessment-load',    [UserProjectController::class, 'reportAssessmentLoad'])->name('reports.assessment.load');

    // User Assessments — list, verify (checklist), report
    Route::prefix('user-assessments')->name('assessment.')->controller(UserAssessmentController::class)->group(function () {
        Route::get('/',                        'index')->name('index');
        Route::get('/ajax',                    'datatable')->name('datatable');
        Route::get('/for-project/{projectId}', 'forProject')->name('for-project');
        Route::get('/checklist/{id}',          'verify')->name('checklist');
        Route::post('/checklist/{id}',         'saveVerification')->name('checklist.save');
        Route::post('/submit/{id}',            'submit')->name('submit');
        Route::get('/report/{id}',             'report')->name('report');
    });
});
// ---------------------------------- PUBLIC / WEBSITE ----------------------------------------

Route::get('/', function () {
    return redirect()->route('admin.login');
});

/* Public Project Report (shared via token) */
Route::get('/project/report/{token}', [PublicReportController::class, 'show'])->name('project.public.report');

