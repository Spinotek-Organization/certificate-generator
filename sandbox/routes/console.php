<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\URL;

Artisan::command('certificate:demo', function () {
    return $this->call('certificate-generator:demo');
})->purpose('Terbitkan satu sertifikat contoh.');

Artisan::command('certificate:admin', function () {
    if (! app()->environment('local')) {
        $this->error('Akses admin demo hanya tersedia di sandbox lokal.');
        return 1;
    }

    $this->line(URL::temporarySignedRoute('sandbox.design-login', now()->addMinutes(10)));
})->purpose('Cetak tautan masuk admin lokal yang berlaku 10 menit.');
