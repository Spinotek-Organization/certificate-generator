<?php

namespace Spinotek\CertificateGenerator;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spinotek\CertificateGenerator\Console\DemoCertificateCommand;
use Spinotek\CertificateGenerator\Console\InstallCertificateCommand;

class CertificateServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/certificate-generator.php', 'certificate-generator');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([InstallCertificateCommand::class, DemoCertificateCommand::class]);
        }

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'spinotek-certificates');

        if (! Gate::has('download-certificate')) {
            Gate::define('download-certificate', static function ($user, Certificate $certificate): bool {
                $userId = method_exists($user, 'getKey') ? $user->getKey() : $user->getAuthIdentifier();

                return $certificate->user_id !== null && (string) $userId === (string) $certificate->user_id;
            });
        }

        $this->publishes([
            __DIR__.'/../config/certificate-generator.php' => config_path('certificate-generator.php'),
        ], 'certificate-generator-config');

        $this->publishes([
            __DIR__.'/../resources/templates/certificate.json' => resource_path('certificates/certificate.json'),
        ], 'certificate-template');
    }

    public static function logoDataUri(): string
    {
        static $uri = null;
        if ($uri !== null) {
            return $uri;
        }

        $path = __DIR__.'/../resources/assets/spinotek-logo.png';

        return $uri = is_file($path)
            ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($path))
            : '';
    }
}
