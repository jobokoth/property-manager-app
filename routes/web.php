<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\TenancyController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisteredUserController;

// Authentication routes
Route::get('/login', [AuthenticatedSessionController::class, 'create'])
    ->name('login');


Route::post('/login', [AuthenticatedSessionController::class, 'store']);

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout')
    ->middleware('auth');

// Registration routes (if needed)
// Route::get('/register', [RegisteredUserController::class, 'create'])
//     ->name('register');
//
// Route::post('/register', [RegisteredUserController::class, 'store']);

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::put('/profile/password', 'updatePassword')->name('profile.password.update');
    });

    Route::get('/', function () {
        $user = auth()->user();

        if ($user->hasRole('tenant')) {
            $activeTenancy = \App\Models\Tenancy::with('unit.property')
                ->where('tenant_user_id', $user->id)
                ->where('status', 'active')
                ->first();

            $serviceRequestsCount = \App\Models\ServiceRequest::where('tenant_user_id', $user->id)->count();
            $mpesaMessagesCount = \App\Models\MpesaMessage::where('tenant_user_id', $user->id)->count();
            $statementsCount = $activeTenancy
                ? \App\Models\Statement::where('tenancy_id', $activeTenancy->id)->count()
                : 0;

            $recentServiceRequests = \App\Models\ServiceRequest::where('tenant_user_id', $user->id)
                ->latest()
                ->take(5)
                ->get();

            return view('dashboard', compact(
                'activeTenancy',
                'serviceRequestsCount',
                'mpesaMessagesCount',
                'statementsCount',
                'recentServiceRequests'
            ));
        }

        // Load dashboard with stats
        $propertiesCount = \App\Models\Property::count();
        $unitsCount = \App\Models\Unit::count();
        $tenanciesCount = \App\Models\Tenancy::where('status', 'active')->count();
        $recentPaymentsCount = \App\Models\Payment::where('status', 'confirmed')->count();
        $recentProperties = \App\Models\Property::latest()->take(5)->get();
        $recentPayments = \App\Models\Payment::with('tenancy.tenant')->where('status', 'confirmed')->latest()->take(5)->get();

        return view('dashboard', compact('propertiesCount', 'unitsCount', 'tenanciesCount', 'recentPaymentsCount', 'recentProperties', 'recentPayments'));
    })->name('dashboard');

    // Property routes
    Route::resource('properties', PropertyController::class);
    Route::post('properties/{property}/assign-manager', [PropertyController::class, 'assignManager'])->name('properties.assign-manager');
    Route::delete('properties/{property}/remove-manager/{manager}', [PropertyController::class, 'removeManager'])->name('properties.remove-manager');
    Route::post('properties/{property}/assign-vendor', [PropertyController::class, 'assignVendor'])->name('properties.assign-vendor');
    Route::delete('properties/{property}/remove-vendor/{vendor}', [PropertyController::class, 'removeVendor'])->name('properties.remove-vendor');

    // Unit routes
    Route::resource('units', UnitController::class);

    // Tenancy routes
    Route::resource('tenancies', TenancyController::class);

    // Payment routes
    Route::resource('payments', PaymentController::class);
    Route::get('payments/mpesa-upload', [PaymentController::class, 'showMpesaUpload'])->name('payments.mpesa-upload');
    Route::post('payments/mpesa-upload', [PaymentController::class, 'processMpesaUpload'])->name('payments.mpesa-upload.process');

    // Service Request routes
    Route::resource('service-requests', ServiceRequestController::class);
    Route::post('service-requests/{serviceRequest}/assign-vendor', [ServiceRequestController::class, 'assignVendor'])->name('service-requests.assign-vendor');
    Route::delete('service-requests/{serviceRequest}/remove-vendor', [ServiceRequestController::class, 'removeVendor'])->name('service-requests.remove-vendor');
    Route::post('service-requests/{serviceRequest}/quotes/{quote}/approve', [ServiceRequestController::class, 'approveQuote'])->name('service-requests.approve-quote');
    Route::post('service-requests/{serviceRequest}/quotes/{quote}/reject', [ServiceRequestController::class, 'rejectQuote'])->name('service-requests.reject-quote');

    // Mpesa Message routes
    Route::resource('mpesa-messages', \App\Http\Controllers\MpesaMessageController::class);

    // Vendor routes
    Route::prefix('vendor')->name('vendor.')->group(function () {
        Route::get('dashboard', [VendorController::class, 'dashboard'])->name('dashboard');
        Route::get('quotes', [VendorController::class, 'quotes'])->name('quotes');
        Route::get('invoices', [VendorController::class, 'invoices'])->name('invoices');
        Route::get('payments', [VendorController::class, 'payments'])->name('payments');
        Route::post('service-requests/{serviceRequest}/quote', [VendorController::class, 'submitQuote'])->name('submit-quote');
        Route::post('service-requests/{serviceRequest}/invoice', [VendorController::class, 'submitInvoice'])->name('submit-invoice');
        Route::post('payments/{payment}/confirm', [VendorController::class, 'confirmPayment'])->name('confirm-payment');
    });

    // Admin routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', AdminUserController::class);
        Route::post('users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
    });
});
