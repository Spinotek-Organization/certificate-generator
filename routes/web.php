<?php

use Illuminate\Support\Facades\Route;
use Spinotek\CertificateGenerator\DesignEditor;
use Spinotek\CertificateGenerator\Http\Controllers\DownloadCertificateController;
use Spinotek\CertificateGenerator\VerifyCertificate;

$verifyPrefix = trim((string) config('certificate-generator.verification_prefix', 'certificates/verify'), '/');
$downloadPrefix = trim((string) config('certificate-generator.download_prefix', 'certificates/download'), '/');
$editorPrefix = trim((string) config('certificate-generator.editor_prefix', 'certificates/design'), '/');

Route::middleware(['web', 'throttle:60,1'])->group(function () use ($verifyPrefix): void {
    Route::get($verifyPrefix, VerifyCertificate::class)->name('spinotek.certificates.verify.index');
    Route::get($verifyPrefix.'/{code}', VerifyCertificate::class)->name('spinotek.certificates.verify');
});

Route::middleware(config('certificate-generator.download_middleware', ['web']))
    ->get($downloadPrefix.'/{code}', DownloadCertificateController::class)
    ->whereUuid('code')
    ->name('spinotek.certificates.download');

Route::middleware(config('certificate-generator.editor_middleware', ['web', 'auth', 'can:manage-certificate-design']))
    ->prefix($editorPrefix)
    ->name('spinotek.design.')
    ->group(function (): void {
        Route::get('/', [DesignEditor::class, 'index'])->name('index');
        Route::post('/', [DesignEditor::class, 'store'])->name('store');
        Route::get('/editor/{design?}', [DesignEditor::class, 'edit'])->name('edit');
        Route::post('/editor/background', [DesignEditor::class, 'upload'])->name('upload');
        Route::get('/editor/preset/{key}', [DesignEditor::class, 'preset'])->name('preset');
        Route::post('/editor/preview', [DesignEditor::class, 'preview'])->name('preview');
        Route::put('/editor/{design?}', [DesignEditor::class, 'save'])->name('save');
        Route::post('/{design}/duplicate', [DesignEditor::class, 'duplicate'])->name('duplicate');
        Route::post('/{design}/default', [DesignEditor::class, 'setDefault'])->name('default');
        Route::delete('/{design}', [DesignEditor::class, 'destroy'])->name('destroy');
    });
