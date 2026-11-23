<?php

namespace Spinotek\CertificateGenerator\Console;

use Illuminate\Console\Command;

class InstallCertificateCommand extends Command
{
    protected $signature = 'certificate-generator:install
        {--force : Jalankan migrasi di production tanpa prompt Laravel}';

    protected $description = 'Siapkan tabel Spinotek Certificate Generator.';

    public function handle(): int
    {
        $result = $this->call('migrate', [
            '--path' => [__DIR__.'/../../database/migrations'],
            '--realpath' => true,
            '--force' => $this->option('force'),
        ]);

        if ($result !== self::SUCCESS) {
            return $result;
        }

        $this->info('Tabel Spinotek Certificate Generator siap.');
        $this->line('Atur APP_URL ke alamat aplikasi yang dapat dibuka penerima sertifikat.');

        if ($this->laravel->environment('local', 'testing')) {
            $this->line('Coba PDF pertama dengan php artisan certificate-generator:demo');
        }

        return self::SUCCESS;
    }
}
