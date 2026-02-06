<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\TenancyController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\ServiceRequestCommentController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CaretakerController;
use App\Http\Controllers\CaretakerTaskController;
use App\Http\Controllers\TenantInviteController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\MessageController;

// Authentication routes
Route::get('/login', [AuthenticatedSessionController::class, 'create'])
    ->name('login');


Route::post('/login', [AuthenticatedSessionController::class, 'store']);

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout')
    ->middleware('auth');

// Registration routes
Route::get('/register', [RegisteredUserController::class, 'create'])
    ->name('register');

Route::post('/register', [RegisteredUserController::class, 'store']);

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

    // Statement routes
    Route::get('statements', [\App\Http\Controllers\StatementController::class, 'index'])->name('statements.index');
    Route::get('statements/{statement}', [\App\Http\Controllers\StatementController::class, 'show'])->name('statements.show');
    Route::post('statements/generate', [\App\Http\Controllers\StatementController::class, 'generate'])->name('statements.generate');

    // Notification routes
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{delivery}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Message routes (for sending messages)
    Route::resource('messages', MessageController::class)->only(['index', 'create', 'store', 'show']);

    // Water management routes
    Route::prefix('water')->name('water.')->group(function () {
        Route::get('/', [\App\Http\Controllers\WaterController::class, 'index'])->name('index');
        Route::get('meters', [\App\Http\Controllers\WaterController::class, 'meters'])->name('meters');
        Route::get('meters/create', [\App\Http\Controllers\WaterController::class, 'createMeter'])->name('meters.create');
        Route::post('meters', [\App\Http\Controllers\WaterController::class, 'storeMeter'])->name('meters.store');
        Route::get('readings', [\App\Http\Controllers\WaterController::class, 'readings'])->name('readings');
        Route::get('readings/create', [\App\Http\Controllers\WaterController::class, 'createReading'])->name('readings.create');
        Route::post('readings', [\App\Http\Controllers\WaterController::class, 'storeReading'])->name('readings.store');
        Route::get('charges', [\App\Http\Controllers\WaterController::class, 'charges'])->name('charges');
        Route::get('charges/create', [\App\Http\Controllers\WaterController::class, 'createCharge'])->name('charges.create');
        Route::post('charges', [\App\Http\Controllers\WaterController::class, 'storeCharge'])->name('charges.store');
        Route::post('generate-bill', [\App\Http\Controllers\WaterController::class, 'generateBill'])->name('generate-bill');
    });

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

    // Caretaker Management routes
    Route::resource('caretakers', CaretakerController::class);

    // Caretaker Tasks routes
    Route::resource('caretaker-tasks', CaretakerTaskController::class);
    Route::post('caretaker-tasks/{caretakerTask}/complete', [CaretakerTaskController::class, 'complete'])->name('caretaker-tasks.complete');
    Route::get('my-tasks', [CaretakerTaskController::class, 'myTasks'])->name('caretaker-tasks.my-tasks');

    // Tenant Invites routes
    Route::resource('tenant-invites', TenantInviteController::class)->except(['edit', 'update', 'destroy']);
    Route::post('tenant-invites/{tenantInvite}/resend', [TenantInviteController::class, 'resend'])->name('tenant-invites.resend');
    Route::post('tenant-invites/{tenantInvite}/cancel', [TenantInviteController::class, 'cancel'])->name('tenant-invites.cancel');

    // Service Request Comments routes
    Route::post('service-requests/{serviceRequest}/comments', [ServiceRequestCommentController::class, 'store'])->name('service-requests.comments.store');
    Route::delete('service-requests/{serviceRequest}/comments/{comment}', [ServiceRequestCommentController::class, 'destroy'])->name('service-requests.comments.destroy');

    // PM User Management routes
    Route::prefix('manage')->name('manage.')->group(function () {
        Route::get('tenants', [UserManagementController::class, 'tenantsIndex'])->name('tenants.index');
        Route::get('tenants/create', [UserManagementController::class, 'tenantsCreate'])->name('tenants.create');
        Route::post('tenants', [UserManagementController::class, 'tenantsStore'])->name('tenants.store');
        Route::get('tenants/{tenant}/edit', [UserManagementController::class, 'tenantsEdit'])->name('tenants.edit');
        Route::put('tenants/{tenant}', [UserManagementController::class, 'tenantsUpdate'])->name('tenants.update');

        Route::get('vendors', [UserManagementController::class, 'vendorsIndex'])->name('vendors.index');
        Route::get('vendors/create', [UserManagementController::class, 'vendorsCreate'])->name('vendors.create');
        Route::post('vendors', [UserManagementController::class, 'vendorsStore'])->name('vendors.store');
        Route::get('vendors/{vendor}/edit', [UserManagementController::class, 'vendorsEdit'])->name('vendors.edit');
        Route::put('vendors/{vendor}', [UserManagementController::class, 'vendorsUpdate'])->name('vendors.update');
    });
});

// Public route for accepting tenant invites (uses signed URL)
Route::get('invite/accept/{invite}', [TenantInviteController::class, 'acceptForm'])
    ->name('tenant-invites.accept')
    ->middleware('signed');

Route::post('invite/accept/{invite}', [TenantInviteController::class, 'accept'])
    ->name('tenant-invites.accept.process');
