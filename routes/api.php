<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\OrganizationProvisioningController;
use App\Http\Controllers\Api\V1\PlatformSettingController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\SubscriptionPlanController;
use App\Http\Controllers\Api\V1\TenantCrudController;
use App\Models\Application;
use App\Models\Candidate;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/users', [AuthController::class, 'createUser']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::get('/dashboard', function (Request $request) {
            $org = $request->user()->organization_id;

            return ['jobs' => Job::where('organization_id', $org)->count(), 'candidates' => Candidate::where('organization_id', $org)->count(), 'applications' => Application::where('organization_id', $org)->count(), 'pipeline' => Application::where('organization_id', $org)->selectRaw('stage, count(*) as total')->groupBy('stage')->pluck('total', 'stage')];
        });
        Route::get('/departments', [TenantCrudController::class, 'departments']);
        Route::post('/departments', [TenantCrudController::class, 'storeDepartment']);
        Route::put('/departments/{department}', [TenantCrudController::class, 'updateDepartment']);
        Route::delete('/departments/{department}', [TenantCrudController::class, 'deleteDepartment']);
        Route::get('/jobs', [TenantCrudController::class, 'jobs']);
        Route::post('/jobs', [TenantCrudController::class, 'storeJob']);
        Route::put('/jobs/{job}', [TenantCrudController::class, 'updateJob']);
        Route::delete('/jobs/{job}', [TenantCrudController::class, 'deleteJob']);
        Route::get('/candidates', [TenantCrudController::class, 'candidates']);
        Route::post('/candidates', [TenantCrudController::class, 'storeCandidate']);
        Route::put('/candidates/{candidate}', [TenantCrudController::class, 'updateCandidate']);
        Route::delete('/candidates/{candidate}', [TenantCrudController::class, 'deleteCandidate']);
        Route::get('/applications', [TenantCrudController::class, 'applications']);
        Route::post('/applications', [TenantCrudController::class, 'storeApplication']);
        Route::put('/applications/{application}', [TenantCrudController::class, 'updateApplication']);
        Route::delete('/applications/{application}', [TenantCrudController::class, 'deleteApplication']);
        Route::get('/users', [TenantCrudController::class, 'users']);
        Route::put('/users/{user}', [TenantCrudController::class, 'updateUser']);
        Route::delete('/users/{user}', [TenantCrudController::class, 'deleteUser']);
        Route::middleware('super_admin')->prefix('superadmin')->group(function () {
            Route::get('/platform-settings', [PlatformSettingController::class, 'show']);
            Route::post('/platform-settings', [PlatformSettingController::class, 'update']);
            Route::apiResource('plans', SubscriptionPlanController::class)->parameters(['plans' => 'subscriptionPlan']);
            Route::apiResource('organizations', OrganizationProvisioningController::class);
        });
    });
});
