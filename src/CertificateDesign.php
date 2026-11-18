<?php

namespace Spinotek\CertificateGenerator;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use UnexpectedValueException;

class CertificateDesign
{
    public static function current(): array
    {
        $saved = DB::table('spinotek_certificate_designs')->where('id', 1)->value('layout');
        if ($saved !== null) {
            return json_decode($saved, true, flags: JSON_THROW_ON_ERROR);
        }

        $path = resource_path('certificates/certificate.json');
        $default = is_file($path) ? $path : __DIR__.'/../resources/templates/certificate.json';

        return File::json(config('certificate-generator.template', $default), JSON_THROW_ON_ERROR);
    }

    public static function save(array $layout): void
    {
        DB::table('spinotek_certificate_designs')->upsert([
            'id' => 1, 'layout' => json_encode($layout, JSON_THROW_ON_ERROR),
        ], ['id'], ['layout']);
    }

    public static function checkGeometry(array $layout): void
    {
        if ($layout['version'] !== 1 || $layout['unit'] !== 'mm') {
            throw new UnexpectedValueException('Template memerlukan format versi 1 dengan satuan mm.');
        }

        $count = 0;
        foreach ($layout['elements'] as $element) {
            ['x' => $x, 'y' => $y, 'width' => $w, 'height' => $h] = $element;
            if ($x < 0 || $y < 0 || $w <= 0 || $h <= 0 ||
                round($x + $w, 8) > $layout['page']['width'] || round($y + $h, 8) > $layout['page']['height']) {
                throw new UnexpectedValueException('Elemen di luar halaman: '.$element['id']);
            }
            if ($element['type'] === 'qr') {
                $count++;
                if ($w != $h || $w < 30) {
                    throw new UnexpectedValueException('QR harus persegi dengan sisi minimal 30 mm.');
                }
            }
        }
        if ($count !== 1) {
            throw new UnexpectedValueException('Desain harus memiliki tepat satu QR verifikasi.');
        }
    }

    public static function checkBackground(string $uri): void
    {
        $parts = explode(',', $uri, 2);
        $types = ['data:image/jpeg;base64' => IMAGETYPE_JPEG, 'data:image/png;base64' => IMAGETYPE_PNG];
        $bytes = isset($types[$parts[0]], $parts[1]) ? base64_decode($parts[1], true) : false;
        $info = $bytes && strlen($bytes) <= 1048576 ? @getimagesizefromstring($bytes) : false;
        if (! $info || $info[2] !== $types[$parts[0]] || $info[0] * $info[1] > 9000000 || ! @imagecreatefromstring($bytes)) {
            throw new UnexpectedValueException('Gunakan JPG atau PNG yang dapat dibaca, maksimal 1 MiB dan 9 megapiksel.');
        }
    }
}
