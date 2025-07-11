<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Inertia::share([
            'auth' => function () {
                return [
                    'user' => $user ? $user->load('roles') : null,
                    'can' => [
                        'createPrograms' => Gate::allows('create programs'),
                        'viewPrograms' => Gate::allows('view programs'),
                    ],
                ];
            },
        ]);
    }
}
