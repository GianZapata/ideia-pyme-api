<?php

namespace App\Providers;

use App\Models\Subscription;
use Illuminate\Support\ServiceProvider;
use Faker\Generator as FakerGenerator;
use PhpCfdi\Rfc\RfcFaker;
use App\Models\User;
use Laravel\Cashier\Cashier;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->extend(FakerGenerator::class, function($generator) {
            $generator->addProvider(new RfcFaker());
            return $generator;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Cashier::useCustomerModel(User::class);
        Cashier::calculateTaxes();
        Cashier::useSubscriptionModel(Subscription::class);
    }
}
