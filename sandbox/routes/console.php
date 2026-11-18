<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
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
