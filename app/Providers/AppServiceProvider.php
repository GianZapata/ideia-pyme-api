<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Faker\Generator as FakerGenerator;
use PhpCfdi\Rfc\RfcFaker;


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
        //
    }
}
