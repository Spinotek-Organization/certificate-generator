<?php

namespace Spinotek\CertificateGenerator;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VerifyCertificate
{
    public function __invoke(Request $request, ?string $code = null): Response
    {
        $query = trim((string) ($code ?? $request->query('q', $request->query('number', ''))));
        $searched = $query !== '';
        $certificate = null;

        if ($searched) {
            $certificate = Certificate::query()
                ->where('code', $query)
                ->orWhere('certificate_number', $query)
                ->first();
        }

        $publicVariableLabels = [];
        $allowedPublic = config('certificate-generator.public_variables', []);
        if (is_array($allowedPublic) && $allowedPublic !== []) {
            $allVars = Support\VariableRegistry::all();
            foreach ($allowedPublic as $key) {
                if (is_string($key) && isset($allVars[$key])) {
                    $publicVariableLabels[$key] = $allVars[$key];
                }
            }
        }

        $status = ($searched && ! $certificate && $code !== null) ? 404 : 200;

        return response()->view('spinotek-certificates::verify', [
            'certificate' => $certificate,
            'query' => $query,
            'searched' => $searched,
            'publicVariableLabels' => $publicVariableLabels,
        ], $status);
    }
}
