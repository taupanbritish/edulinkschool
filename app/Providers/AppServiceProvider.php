<?php

namespace App\Providers;

use App\Support\School;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('*', function ($view) {
            if (! array_key_exists('school', $view->getData())) {
                $view->with('school', School::all());
            }
        });
    }
}
