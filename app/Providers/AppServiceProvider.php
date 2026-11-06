<?php

namespace App\Providers;

use App\Contracts\PaymentServiceInterface;
use App\Models\Order;
use App\Models\User;
use App\Policies\OrderPolicy;
use App\Services\AI\AetherealAIService;
use App\Services\AI\AIServiceInterface;
use App\Services\MockPaymentService;
use App\Services\Payment\MidtransPaymentService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentServiceInterface::class, function ($app) {
            $driver = config('services.payment.driver', 'mock');

            return match ($driver) {
                'midtrans' => $app->make(MidtransPaymentService::class),
                default => $app->make(MockPaymentService::class),
            };
        });

        $this->app->bind(
            AIServiceInterface::class,
            AetherealAIService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Order::class, OrderPolicy::class);

        Gate::define('admin', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('joki', function (User $user) {
            return $user->isJoki() || $user->isAdmin();
        });

        Gate::define('customer', function (User $user) {
            return $user->isCustomer();
        });
    }
}
