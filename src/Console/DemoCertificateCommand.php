<?php

namespace Spinotek\CertificateGenerator\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Spinotek\CertificateGenerator\CertificateGenerator;

class DemoCertificateCommand extends Command
{
    protected $signature = 'certificate-generator:demo';

    protected $description = 'Terbitkan PDF contoh dan tampilkan tautan verifikasinya.';

    public function handle(CertificateGenerator $generator): int
    {
        if (! $this->laravel->environment('local', 'testing')) {
            $this->error('Demo hanya tersedia ketika APP_ENV=local atau APP_ENV=testing.');

            return self::FAILURE;
        }

        if (! Schema::hasTable('spinotek_certificates') || ! Schema::hasTable('spinotek_certificate_designs')) {
            $this->error('Tabel sertifikat belum tersedia. Jalankan php artisan certificate-generator:install atau php artisan migrate terlebih dahulu.');

            return self::FAILURE;
        }

        $directory = storage_path('app/certificates');
        File::ensureDirectoryExists($directory);

        $path = null;
        try {
            $result = DB::transaction(function () use ($generator, $directory, &$path): array {
                $certificate = $generator->issue([
                    'category' => 'event',
                    'participant_name' => 'Rani Putri',
                    'activity_title' => 'Lokakarya Desain Informasi',
                    'issued_date' => today()->toDateString(),
                ]);

                $path = $directory.'/'.$certificate->code.'.pdf';
                $pdf = $certificate->renderPdf();
                if (File::put($path, $pdf) !== strlen($pdf)) {
                    throw new RuntimeException('PDF demo gagal disimpan.');
                }

                return [
                    'path' => $path,
                    'number' => $certificate->certificate_number,
                    'url' => $generator->verificationUrl($certificate->code),
                ];
            });
        } catch (\Throwable $exception) {
            if ($path !== null) {
                File::delete($path);
            }
            throw $exception;
        }

        $this->line('PDF: '.$result['path']);
        $this->line('Nomor: '.$result['number']);
        $this->line('Verifikasi: '.$result['url']);

        return self::SUCCESS;
    }
}
