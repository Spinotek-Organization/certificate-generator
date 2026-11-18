<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class SandboxServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('manage-certificate-design', fn (?Authenticatable $user) =>
            app()->environment('local') && session('sandbox.design-admin') === true);
    }
}
