<?php

use Illuminate\Support\Facades\Route;
use Spinotek\CertificateGenerator\VerifyCertificate;

Route::get('/certificates/verify/{code}', VerifyCertificate::class)
    ->whereUuid('code')
    ->name('spinotek.certificates.verify');
