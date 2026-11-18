<?php

namespace Spinotek\CertificateGenerator;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Mpdf\Mpdf;
use UnexpectedValueException;

class CertificatePdf
{
    public static function qrImage(string $url): string
    {
        return (new PngWriter)->write(new QrCode($url, errorCorrectionLevel: ErrorCorrectionLevel::Medium, size: 600, margin: 80))->getString();
    }

    public function render(array $fields, string $url, ?array $layout = null): string
    {
        $layout ??= CertificateDesign::current();
        CertificateDesign::checkGeometry($layout);
        ['width' => $width, 'height' => $height] = $layout['page'];
        $pdf = new Mpdf(['format' => [$width, $height], 'tempDir' => storage_path('framework/certificate-generator')]);
        $pdf->SetTitle('Sertifikat Partisipasi');
        $pdf->AddPage();
        if (! empty($layout['background'])) {
            $pdf->imageVars['background'] = base64_decode(explode(',', $layout['background'], 2)[1], true);
            $pdf->Image('var:background', 0, 0, $width, $height, constrain: false, allowvector: false);
        }
        if ($layout['frame'] ?? true) {
            $pdf->SetDrawColor(51, 81, 72);
            $pdf->Rect(8, 8, $width - 16, $height - 16);
        }

        foreach ($layout['elements'] as $element) {
            if ($element['type'] === 'qr') {
                $qr = $element;
                continue;
            }
            ['x' => $x, 'y' => $y, 'width' => $w, 'height' => $h] = $element;
            $value = match ($element['type']) {
                'text' => $element['text'],
                'variable' => $fields[$element['field']] ?? throw new UnexpectedValueException('Field template tidak dikenal: '.$element['field']),
                default => throw new UnexpectedValueException('Tipe elemen tidak dikenal: '.$element['type']),
            };
            $font = match ($element['font'] ?? 'sans') {
                'serif' => 'dejavuserif',
                'mono' => 'dejavusansmono',
                default => 'dejavusans',
            };
            $align = in_array($element['align'] ?? '', ['center', 'right'], true) ? $element['align'] : 'left';
            $size = (float) $element['font_size'];
            $color = preg_match('/^#[a-f0-9]{6}$/iD', $element['color'] ?? '') ? $element['color'] : '#243d35';
            $style = "font-family: $font; font-size: {$size}pt; text-align: $align; color: $color";
            $content = nl2br(e($value), false);
            $pdf->WriteFixedPosHTML('<div style="'.$style.'">'.$content.'</div>', $x, $y, $w, $h, 'auto');
        }
        $pdf->imageVars['verification'] = self::qrImage($url);
        $pdf->Image('var:verification', $qr['x'], $qr['y'], $qr['width'], $qr['height'], 'png', constrain: false);

        return $pdf->OutputBinaryData();
    }
}
