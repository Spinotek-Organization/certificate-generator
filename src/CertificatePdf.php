<?php

namespace Spinotek\CertificateGenerator;

use Dompdf\Dompdf;
use Dompdf\FontMetrics;
use Dompdf\Options;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\File;
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
        $width = (float) $layout['page']['width'];
        $height = (float) $layout['page']['height'];

        $cache = storage_path('framework/certificate-generator');
        File::ensureDirectoryExists($cache);
        $options = new Options();
        $options->setFontCache($cache);
        $options->setTempDir($cache);
        $options->setAllowedProtocols(['data://']);
        $options->setIsJavascriptEnabled(false);
        $pdf = new Dompdf($options);
        $pointsPerMillimeter = 72 / 25.4;
        $pdf->setPaper([0, 0, $width * $pointsPerMillimeter, $height * $pointsPerMillimeter]);

        $html = '<!doctype html><html><head><meta charset="utf-8"><title>Sertifikat Partisipasi</title>'
            .'<style>@page{margin:0}body{margin:0}img,div{position:absolute}div{overflow:hidden;overflow-wrap:break-word;line-height:1.15}</style></head><body>';

        if (! empty($layout['background'])) {
            CertificateDesign::checkBackground($layout['background']);
            $html .= '<img alt="" style="left:0;top:0;width:'.$width.'mm;height:'.$height.'mm" src="'.e($layout['background']).'">';
        }
        if ($layout['frame'] ?? true) {
            $html .= '<div style="left:8mm;top:8mm;right:8mm;height:'.($height - 16).'mm;border:0.2mm solid #335148"></div>';
        }

        foreach ($layout['elements'] as $element) {
            $box = sprintf('left:%Fmm;top:%Fmm;width:%Fmm;height:%Fmm', $element['x'], $element['y'], $element['width'], $element['height']);
            if ($element['type'] === 'qr') {
                $qrBox = $box;
                continue;
            }
            $value = match ($element['type']) {
                'text' => $element['text'],
                'variable' => $fields[$element['field']] ?? throw new UnexpectedValueException('Field template tidak dikenal: '.$element['field']),
                default => throw new UnexpectedValueException('Tipe elemen tidak dikenal: '.$element['type']),
            };
            $font = match ($element['font'] ?? 'sans') {
                'serif' => 'DejaVu Serif',
                'mono' => 'DejaVu Sans Mono',
                default => 'DejaVu Sans',
            };
            $align = in_array($element['align'] ?? '', ['center', 'right'], true) ? $element['align'] : 'left';
            $size = $this->fitText($pdf->getFontMetrics(), $font, (string) $value, (float) $element['font_size'],
                $element['width'] * $pointsPerMillimeter, $element['height'] * $pointsPerMillimeter);
            $color = preg_match('/^#[a-f0-9]{6}$/iD', $element['color'] ?? '') ? $element['color'] : '#243d35';
            $html .= '<div style="'.$box.';font-family:'.$font.';font-size:'.$size.'pt;text-align:'.$align.';color:'.$color.'">'
                .nl2br(e($value), false).'</div>';
        }

        $qr = base64_encode(self::qrImage($url));
        $html .= '<img style="'.$qrBox.'" alt="" src="data:image/png;base64,'.$qr.'"></body></html>';

        $pdf->loadHtml($html, 'UTF-8');
        $pdf->render();

        return $pdf->output();
    }

    private function fitText(FontMetrics $metrics, string $family, string $value, float $size, float $width, float $height): float
    {
        $font = $metrics->getFont($family, 'normal');
        $lineHeight = $metrics->getFontHeight($font, 1) * 1.15;
        $space = $metrics->getTextWidth(' ', $font, 1);
        $paragraphs = [];
        foreach (preg_split('/\R/u', $value) as $paragraph) {
            $words = preg_split('/[ \t]+/u', trim($paragraph), flags: PREG_SPLIT_NO_EMPTY);
            $paragraphs[] = array_map(fn ($word) => $metrics->getTextWidth($word, $font, 1), $words);
        }

        $fits = static function (float $points) use ($paragraphs, $space, $lineHeight, $width, $height): bool {
            $lines = count($paragraphs);
            foreach ($paragraphs as $words) {
                $used = 0.0;
                foreach ($words as $word) {
                    if ($word * $points > $width) {
                        return false;
                    }
                    $next = $used > 0 ? $used + $space + $word : $word;
                    if ($next * $points > $width) {
                        $lines++;
                        $next = $word;
                    }
                    $used = $next;
                }
            }

            return $lines * $lineHeight * $points <= $height;
        };

        if ($fits($size)) {
            return $size;
        }
        $lower = 0.0;
        $upper = $size;
        for ($i = 0; $i < 20; $i++) {
            $middle = ($lower + $upper) / 2;
            if ($fits($middle)) {
                $lower = $middle;
            } else {
                $upper = $middle;
            }
        }

        return $lower;
    }
}
