<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Notify\AuthorityNotifier;
use App\Services\Notify\EmailAuthorityNotifier;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthorityNotifier::class, EmailAuthorityNotifier::class);
    }

    public function boot(): void
    {
        Gate::define('access-admin', fn (User $user) => $user->role->isAdminPanel());
        Gate::define('access-superadmin', fn (User $user) => $user->role === UserRole::Superadmin);
        Gate::define('verify-reports', fn (User $user) => in_array($user->role, [UserRole::Verifikator, UserRole::Admin, UserRole::Superadmin], true));

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
