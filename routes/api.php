<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group with a prefix of "api/v1".
|
*/

// Public authentication routes
Route::prefix('auth')->group(function () {
    Route::post('login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
    Route::post('register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
});

// Protected routes requiring authentication
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
        Route::get('user', [\App\Http\Controllers\Api\AuthController::class, 'user']);
    });

    // Tenant routes
    Route::prefix('tenant')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Api\TenantController::class, 'dashboard']);
        Route::get('tenancy', [\App\Http\Controllers\Api\TenantController::class, 'tenancy']);
        Route::get('balances', [\App\Http\Controllers\Api\TenantController::class, 'balances']);
        Route::get('statements', [\App\Http\Controllers\Api\TenantController::class, 'statements']);
        Route::get('statements/{statement}', [\App\Http\Controllers\Api\TenantController::class, 'showStatement']);

        // Mpesa message upload
        Route::post('mpesa-messages', [\App\Http\Controllers\Api\TenantController::class, 'uploadMpesaMessage']);
        Route::get('mpesa-messages', [\App\Http\Controllers\Api\TenantController::class, 'mpesaMessages']);

        // Service requests
        Route::get('service-requests', [\App\Http\Controllers\Api\TenantController::class, 'serviceRequests']);
        Route::post('service-requests', [\App\Http\Controllers\Api\TenantController::class, 'createServiceRequest']);
        Route::get('service-requests/{serviceRequest}', [\App\Http\Controllers\Api\TenantController::class, 'showServiceRequest']);
    });

    // Manager routes
    Route::prefix('manager')->group(function () {
        // Route::apiResource('properties', \App\Http\Controllers\Api\PropertyController::class);
        // Route::apiResource('properties.units', \App\Http\Controllers\Api\UnitController::class)->shallow();
        // Route::apiResource('tenancies', \App\Http\Controllers\Api\TenancyController::class);
        // Route::apiResource('payments', \App\Http\Controllers\Api\PaymentController::class);
        // Route::apiResource('service-requests', \App\Http\Controllers\Api\ServiceRequestController::class);

        // Mpesa inbox
        // Route::get('mpesa-inbox', [\App\Http\Controllers\Api\MpesaController::class, 'inbox']);
        // Route::post('mpesa-inbox/{mpesaMessage}/approve', [\App\Http\Controllers\Api\MpesaController::class, 'approve']);
        // Route::post('mpesa-inbox/{mpesaMessage}/reject', [\App\Http\Controllers\Api\MpesaController::class, 'reject']);

        // Messaging
        // Route::apiResource('messages', \App\Http\Controllers\Api\MessageController::class);
    });

    // Vendor routes
    Route::prefix('vendor')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Api\VendorApiController::class, 'dashboard']);
        Route::get('jobs', [\App\Http\Controllers\Api\VendorApiController::class, 'jobs']);
        Route::get('jobs/{serviceRequest}', [\App\Http\Controllers\Api\VendorApiController::class, 'showJob']);
        Route::post('jobs/{serviceRequest}/quote', [\App\Http\Controllers\Api\VendorApiController::class, 'submitQuote']);
        Route::post('jobs/{serviceRequest}/schedule', [\App\Http\Controllers\Api\VendorApiController::class, 'submitSchedule']);
        Route::post('jobs/{serviceRequest}/invoice', [\App\Http\Controllers\Api\VendorApiController::class, 'submitInvoice']);
        Route::get('invoices', [\App\Http\Controllers\Api\VendorApiController::class, 'invoices']);
        Route::get('payments', [\App\Http\Controllers\Api\VendorApiController::class, 'payments']);
        Route::post('payments/{vendorPayment}/confirm', [\App\Http\Controllers\Api\VendorApiController::class, 'confirmPayment']);
    });
});
