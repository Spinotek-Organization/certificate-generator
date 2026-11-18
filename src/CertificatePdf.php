<?php

namespace Spinotek\CertificateGenerator;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\File;
use Mpdf\Mpdf;
use UnexpectedValueException;

class CertificatePdf
{
    public function render(array $fields, string $url): string
    {
        $path = resource_path('certificates/certificate.json');
        $default = is_file($path) ? $path : __DIR__.'/../resources/templates/certificate.json';
        $layout = File::json(config('certificate-generator.template', $default), JSON_THROW_ON_ERROR);
        if ($layout['version'] !== 1 || $layout['unit'] !== 'mm') {
            throw new UnexpectedValueException('Template memerlukan format versi 1 dengan satuan mm.');
        }
        ['width' => $width, 'height' => $height] = $layout['page'];
        $pdf = new Mpdf(['format' => [$width, $height], 'tempDir' => storage_path('framework/certificate-generator')]);
        $pdf->SetTitle('Sertifikat Partisipasi');
        $pdf->AddPage();
        $pdf->SetDrawColor(51, 81, 72);
        $pdf->Rect(8, 8, $width - 16, $height - 16);

        $qr = null;
        foreach ($layout['elements'] as $element) {
            ['x' => $x, 'y' => $y, 'width' => $w, 'height' => $h] = $element;
            if ($x < 0 || $y < 0 || $w <= 0 || $h <= 0 || $x + $w > $width || $y + $h > $height) {
                throw new UnexpectedValueException('Elemen template di luar halaman: '.$element['id']);
            }
            if ($element['type'] === 'qr') {
                if ($qr !== null || $w != $h || $w < 30) {
                    throw new UnexpectedValueException('Gunakan satu QR persegi dengan sisi minimal 30 mm.');
                }
                $qr = $element;
                continue;
            }
            $value = match ($element['type']) {
                'text' => $element['text'],
                'variable' => $fields[$element['field']] ?? throw new UnexpectedValueException('Field template tidak dikenal: '.$element['field']),
                default => throw new UnexpectedValueException('Tipe elemen tidak dikenal: '.$element['type']),
            };
            $font = ($element['font'] ?? '') === 'serif' ? 'dejavuserif' : 'dejavusans';
            $align = ($element['align'] ?? '') === 'center' ? 'center' : 'left';
            $size = (float) $element['font_size'];
            $style = "font-family: $font; font-size: {$size}pt; text-align: $align; color: #243d35";
            $pdf->WriteFixedPosHTML('<div style="'.$style.'">'.e($value).'</div>', $x, $y, $w, $h, 'auto');
        }
        if ($qr === null) {
            throw new UnexpectedValueException('Template harus memuat QR verifikasi.');
        }
        $image = (new PngWriter)->write(new QrCode($url, errorCorrectionLevel: ErrorCorrectionLevel::Medium, size: 600, margin: 80));
        $pdf->imageVars['verification'] = $image->getString();
        $pdf->Image('var:verification', $qr['x'], $qr['y'], $qr['width'], $qr['height'], 'png');

        return $pdf->OutputBinaryData();
    }
}
