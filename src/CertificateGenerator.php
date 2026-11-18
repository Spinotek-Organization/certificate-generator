<?php

namespace Spinotek\CertificateGenerator;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CertificateGenerator
{
    public function issue(array $data): Certificate
    {
        $data['issuer'] ??= config('app.name');
        $fields = Validator::make($data, [
            'participant_name' => 'required|string|max:255',
            'activity_title' => 'required|string|max:255',
            'issued_date' => 'required|string|date_format:Y-m-d',
            'issuer' => 'required|string|max:255',
        ])->validate();

        $code = (string) Str::uuid();
        $base = rtrim((string) config('app.url'), '/');
        if (! filter_var($base, FILTER_VALIDATE_URL) || ! in_array(parse_url($base, PHP_URL_SCHEME), ['http', 'https'], true)
            || array_intersect(['user', 'pass', 'query', 'fragment'], array_keys(parse_url($base)))) {
            throw new InvalidArgumentException('APP_URL harus berupa URL dasar HTTP atau HTTPS aplikasi penerbit.');
        }
        $url = $base.route('spinotek.certificates.verify', ['code' => $code], false);
        $fields['status'] = 'Valid';
        $pdf = (new CertificatePdf)->render($fields, $url);

        return Certificate::forceCreate($fields + ['code' => $code, 'pdf_base64' => base64_encode($pdf)]);
    }
}
