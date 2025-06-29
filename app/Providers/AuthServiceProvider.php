<?php


namespace App\Providers;

use App\Models\Parish;
use App\Enums\UserTypeEnum;
use App\Policies\ParishPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Parish::class => ParishPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('access-admin-panel', function ($user) {
            return $user->user_type === 'admin';
        });

        Gate::define('access-member-panel', function ($user) {
            return $user->user_type === 'member';
        });

        Gate::define('access-sec-panel', function ($user) {
            return $user->user_type === 'sec';
        });

        Gate::define('access-presbiterio-panel', function ($user) {
            return $user->user_type === 'presbiterio';
        });

        Gate::define('access-diretoria-panel', function ($user) {
            return $user->user_type === 'diretoria';
        });
    }
}
