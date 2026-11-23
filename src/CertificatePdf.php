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
        $imageSizes = CertificateDesign::imageSizes($layout);
        $theme = CertificateDesign::resolveTheme($layout);
        $showFrame = $layout['frame'] ?? true;
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
            .'<style>@page{margin:0;background:'.$theme['background'].'}body{margin:0;background:'.$theme['background'].'}img,div{position:absolute}div{overflow:hidden;overflow-wrap:break-word;line-height:1.15}</style></head><body>';

        if (! empty($layout['background'])) {
            $html .= '<img alt="" style="left:0;top:0;width:'.$width.'mm;height:'.$height.'mm" src="'.e($layout['background']).'">';
        }
        if ($showFrame) {
            $html .= $this->frameMarkup($width, $height, $theme);
        }

        foreach ($layout['elements'] as $index => $element) {
            $box = sprintf('left:%Fmm;top:%Fmm;width:%Fmm;height:%Fmm', $element['x'], $element['y'], $element['width'], $element['height']);
            if ($element['type'] === 'qr') {
                $qrElement = $element;
                continue;
            }
            if ($element['type'] === 'image') {
                [$imageWidth, $imageHeight] = $imageSizes[$index];
                $scale = min($element['width'] / $imageWidth, $element['height'] / $imageHeight);
                $w = $imageWidth * $scale;
                $h = $imageHeight * $scale;
                $box = sprintf('left:%Fmm;top:%Fmm;width:%Fmm;height:%Fmm',
                    $element['x'] + ($element['width'] - $w) / 2,
                    $element['y'] + ($element['height'] - $h) / 2, $w, $h);
                $html .= '<img alt="" style="'.$box.'" src="'.e($element['image']).'">';
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
            $color = CertificateDesign::isHexColor($element['color'] ?? null) ? $element['color'] : $theme['text'];
            $html .= '<div style="'.$box.';font-family:'.$font.';font-size:'.$size.'pt;text-align:'.$align.';color:'.$color.'">'
                .nl2br(e($value), false).'</div>';
        }

        $qr = base64_encode(self::qrImage($url));
        if (! $theme['legacy'] && $showFrame) {
            $qrStyle = sprintf('left:%Fmm;top:%Fmm;width:%Fmm;height:%Fmm;background:#ffffff;border:0.25mm solid %s',
                $qrElement['x'], $qrElement['y'], $qrElement['width'] - 0.5, $qrElement['height'] - 0.5, $theme['accent']);
        } else {
            $qrStyle = sprintf('left:%Fmm;top:%Fmm;width:%Fmm;height:%Fmm;background:#ffffff',
                $qrElement['x'], $qrElement['y'], $qrElement['width'], $qrElement['height']);
        }
        $html .= '<img style="'.$qrStyle.'" alt="" src="data:image/png;base64,'.$qr.'"></body></html>';

        $pdf->loadHtml($html, 'UTF-8');
        $pdf->render();

        return $pdf->output();
    }

    private function frameMarkup(float $width, float $height, array $theme): string
    {
        if ($theme['legacy']) {
            return '<div style="left:8mm;top:8mm;right:8mm;height:'.($height - 16).'mm;border:0.2mm solid '.$theme['frame'].'"></div>';
        }

        $outer = min(9.0, $width * 0.1, $height * 0.1);
        $inner = $outer + min(3.6, $width * 0.03, $height * 0.03);
        $corner = $inner - $outer;
        $frame = $theme['frame'];
        $accent = $theme['accent'];
        $barW = min(46.0, max(16.0, $width * 0.16));
        $barX = ($width - $barW) / 2;
        $dotSize = 1.6;
        $dotGap = 3.2;

        $parts = [
            sprintf('<div style="left:%Fmm;top:%Fmm;width:%Fmm;height:%Fmm;border:0.75mm solid %s"></div>',
                $outer, $outer, $width - $outer * 2 - 1.5, $height - $outer * 2 - 1.5, $frame),
            sprintf('<div style="left:%Fmm;top:%Fmm;width:%Fmm;height:%Fmm;border:0.3mm solid %s"></div>',
                $inner, $inner, $width - $inner * 2 - 0.6, $height - $inner * 2 - 0.6, $accent),
        ];

        $corners = [
            [$outer, $outer],
            [$width - $inner, $outer],
            [$outer, $height - $inner],
            [$width - $inner, $height - $inner],
        ];
        foreach ($corners as [$cx, $cy]) {
            $parts[] = sprintf('<div style="left:%Fmm;top:%Fmm;width:%Fmm;height:%Fmm;background:%s;border:0.75mm solid %s"></div>',
                $cx, $cy, $corner - 1.5, $corner - 1.5, $accent, $frame);
        }

        // Top and bottom centered ceremonial accent bar with flanking frame stops
        foreach ([$inner + 0.15, $height - $inner - 0.15] as $centerY) {
            $parts[] = sprintf('<div style="left:%Fmm;top:%Fmm;width:%Fmm;height:1.0mm;background:%s"></div>',
                $barX, $centerY - 0.5, $barW, $accent);
            $parts[] = sprintf('<div style="left:%Fmm;top:%Fmm;width:%Fmm;height:%Fmm;background:%s"></div>',
                $barX - $dotGap - $dotSize, $centerY - $dotSize / 2, $dotSize, $dotSize, $frame);
            $parts[] = sprintf('<div style="left:%Fmm;top:%Fmm;width:%Fmm;height:%Fmm;background:%s"></div>',
                $barX + $barW + $dotGap, $centerY - $dotSize / 2, $dotSize, $dotSize, $frame);
        }

        return implode('', $parts);
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
