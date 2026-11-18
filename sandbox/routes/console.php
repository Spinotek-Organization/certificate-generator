<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Spinotek\CertificateGenerator\CertificateGenerator;

Artisan::command('certificate:demo', function (CertificateGenerator $generator) {
    $certificate = $generator->issue([
        'participant_name' => 'Rani Putri',
        'activity_title' => 'Lokakarya Desain Informasi',
        'issued_date' => today()->toDateString(),
    ]);

    $directory = storage_path('app/certificates');
    File::ensureDirectoryExists($directory);
    $path = $directory.'/'.$certificate->code.'.pdf';
    File::put($path, $certificate->download()->getContent());

    $this->line($path);
    $this->line(route('spinotek.certificates.verify', $certificate->code));
})->purpose('Terbitkan satu sertifikat contoh dan tampilkan lokasi PDF serta URL verifikasinya.');

Artisan::command('certificate:admin', function () {
    if (! app()->environment('local')) {
        $this->error('Akses admin demo hanya tersedia di sandbox lokal.');
        return 1;
    }

    $this->line(URL::temporarySignedRoute('sandbox.design-login', now()->addMinutes(10)));
})->purpose('Cetak tautan masuk admin lokal yang berlaku 10 menit.');
