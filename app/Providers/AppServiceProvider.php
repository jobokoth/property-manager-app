<?php

namespace App\Providers;

use App\Exceptions\Handler;
use App\Models\MessageDelivery;
use App\Models\MpesaMessage;
use App\Models\Payment;
use App\Models\Property;
use App\Models\ServiceRequest;
use App\Models\Tenancy;
use App\Models\Unit;
use App\Policies\MpesaMessagePolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PropertyPolicy;
use App\Policies\ServiceRequestPolicy;
use App\Policies\TenancyPolicy;
use App\Policies\UnitPolicy;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     */
    protected array $policies = [
        Property::class => PropertyPolicy::class,
        Unit::class => UnitPolicy::class,
        Tenancy::class => TenancyPolicy::class,
        Payment::class => PaymentPolicy::class,
        ServiceRequest::class => ServiceRequestPolicy::class,
        MpesaMessage::class => MpesaMessagePolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ExceptionHandler::class, Handler::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        // Super admin bypass for all gates
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('super_admin')) {
                return true;
            }
        });

        // Share notification data with layout
        View::composer('layouts.app', function ($view) {
            if (auth()->check()) {
                $unreadCount = MessageDelivery::unreadForUser(auth()->id())->count();
                $recentNotifications = MessageDelivery::forUser(auth()->id())
                    ->with('message')
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get();

                $view->with('unreadNotificationCount', $unreadCount);
                $view->with('unreadMessageCount', $unreadCount);
                $view->with('recentNotifications', $recentNotifications);
            } else {
                $view->with('unreadNotificationCount', 0);
                $view->with('unreadMessageCount', 0);
                $view->with('recentNotifications', collect());
            }
        });
    }
}
