<?php

namespace Spinotek\CertificateGenerator;

use Illuminate\Support\ServiceProvider;

class CertificateServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'spinotek-certificates');

        $this->publishes([
            __DIR__.'/../resources/templates/certificate.json' => resource_path('certificates/certificate.json'),
        ], 'certificate-template');
    }
}
