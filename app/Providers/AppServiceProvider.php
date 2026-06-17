<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Services\SimulatedPaymentGateway;
use App\Services\StripePaymentGateway;
use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, function (): PaymentGateway {
            $secret = config('services.stripe.secret');

            if (blank($secret)) {
                return new SimulatedPaymentGateway;
            }

            return new StripePaymentGateway(
                new StripeClient($secret),
                config('services.stripe.currency'),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
