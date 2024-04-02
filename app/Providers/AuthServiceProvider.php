<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //  Gate::define('update-token', function($user,User $user){
        //      return $user->id === $user->id;
        //  });
        Gate::define('admin', function ($user) {
            return $user->role === 'admin';
        });

        Gate::define('user', function ($user) {
            return $user->role === 'user';
        });
        Gate::define('user_and_admin', function ($user) {
            return $user->role === 'user' || $user->role === 'admin';
        });
    }
}
