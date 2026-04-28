<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Policies\OrderPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PharmacyPolicy;
use App\Policies\ProductPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class  => ProductPolicy::class,
        Order::class    => OrderPolicy::class,
        Pharmacy::class => PharmacyPolicy::class,
        Payment::class  => PaymentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define a gate for admin access
        Gate::define('admin', function ($user) {
            return $user->role === 'admin';
        });

        // Define a gate for moderator access
        Gate::define('moderator', function ($user) {
            return $user->role === 'admin' || $user->role === 'moderator';
        });
    }
}
