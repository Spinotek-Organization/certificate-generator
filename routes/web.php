<?php

use Illuminate\Support\Facades\Route;
use Spinotek\CertificateGenerator\DesignEditor;
use Spinotek\CertificateGenerator\VerifyCertificate;

Route::get(trim(config('certificate-generator.verification_prefix'), '/').'/{code}', VerifyCertificate::class)
    ->whereUuid('code')
    ->name('spinotek.certificates.verify');

Route::middleware(['web', 'can:manage-certificate-design'])->prefix(config('certificate-generator.editor_prefix'))->name('spinotek.design.')->group(function () {
    Route::get('/', [DesignEditor::class, 'edit'])->name('edit');
    Route::post('/background', [DesignEditor::class, 'upload'])->name('upload');
    Route::post('/preview', [DesignEditor::class, 'preview'])->name('preview');
    Route::put('/', [DesignEditor::class, 'save'])->name('save');
});
