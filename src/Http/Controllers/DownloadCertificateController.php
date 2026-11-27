<?php

namespace Spinotek\CertificateGenerator\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Spinotek\CertificateGenerator\Certificate;

class DownloadCertificateController
{
    public function __invoke(Request $request, string $code): Response
    {
        $certificate = Certificate::query()
            ->with(['design', 'snapshot'])
            ->where('code', $code)
            ->firstOrFail();

        if (! $request->hasValidSignature()) {
            abort_unless($request->user() !== null, 403, 'Silakan login untuk mengunduh sertifikat.');
            Gate::authorize('download-certificate', $certificate);
        }

        if ($certificate->isRevoked() && ! ($request->user() && Gate::allows('manage-certificate-design'))) {
            abort(403, 'Sertifikat ini telah dicabut dan tidak dapat diunduh.');
        }

        return $certificate->download(inline: $request->boolean('inline'));
    }
}
