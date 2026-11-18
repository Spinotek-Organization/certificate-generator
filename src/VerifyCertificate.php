<?php

namespace Spinotek\CertificateGenerator;

use Illuminate\Http\Response;

class VerifyCertificate
{
    public function __invoke(string $code): Response
    {
        $certificate = Certificate::query()->find($code, [
            'participant_name', 'activity_title', 'issuer', 'issued_date', 'status',
        ]);

        return response()->view('spinotek-certificates::verify', compact('certificate'), $certificate ? 200 : 404);
    }
}
