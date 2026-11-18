<?php

use Illuminate\Support\Facades\Route;
use Spinotek\CertificateGenerator\DesignEditor;
use Spinotek\CertificateGenerator\VerifyCertificate;

Route::get('/certificates/verify/{code}', VerifyCertificate::class)
    ->whereUuid('code')
    ->name('spinotek.certificates.verify');

Route::middleware(['web', 'can:manage-certificate-design'])->prefix('certificates/design')->name('spinotek.design.')->group(function () {
    Route::get('/', [DesignEditor::class, 'edit'])->name('edit');
    Route::post('/background', [DesignEditor::class, 'upload'])->name('upload');
    Route::post('/preview', [DesignEditor::class, 'preview'])->name('preview');
    Route::put('/', [DesignEditor::class, 'save'])->name('save');
});
