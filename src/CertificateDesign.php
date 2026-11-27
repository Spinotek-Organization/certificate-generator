<?php

namespace Spinotek\CertificateGenerator;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spinotek\CertificateGenerator\Models\CertificateDesignRecord;
use UnexpectedValueException;

class CertificateDesign
{
    public const PALETTES = [
        'spinotek-blue' => [
            'label' => 'Spinotek Azure & Cyan',
            'background' => '#f8fbff',
            'accent' => '#0088ff',
            'frame' => '#0b254b',
            'text' => '#0b254b',
            'muted' => '#475e80',
            'accent_text' => '#0052cc',
        ],
        'midnight-navy' => [
            'label' => 'Midnight Navy & Champagne Gold',
            'background' => '#fffdf8',
            'accent' => '#a27d34',
            'frame' => '#14233c',
            'text' => '#14233c',
            'muted' => '#4a586c',
            'accent_text' => '#8a6827',
        ],
        'emerald-ivory' => [
            'label' => 'Emerald & Ivory',
            'background' => '#fbf9f0',
            'accent' => '#9e824b',
            'frame' => '#133a2f',
            'text' => '#133a2f',
            'muted' => '#3d5c52',
            'accent_text' => '#77582c',
        ],
    ];

    public const LEGACY_THEME = [
        'legacy' => true,
        'palette' => 'legacy',
        'background' => '#ffffff',
        'accent' => '#335148',
        'frame' => '#335148',
        'text' => '#243d35',
    ];

    public static function palettes(): array
    {
        return self::PALETTES;
    }

    /**
     * @return list<array{float, float, float, float, 'frame'|'accent'}>
     */
    public static function frameLines(float $width, float $height): array
    {
        $side = min($width, $height);
        $inset = min(9, $side * .10);
        $keyline = .45;
        $right = $width - $inset;
        $bottom = $height - $inset;
        $lines = [
            [$inset, $inset, $right - $inset, $keyline, 'frame'],
            [$inset, $bottom - $keyline, $right - $inset, $keyline, 'frame'],
            [$inset, $inset, $keyline, $bottom - $inset, 'frame'],
            [$right - $keyline, $inset, $keyline, $bottom - $inset, 'frame'],
        ];

        $gap = min(2, $side * .045);
        $length = min(13, $side * .19);
        $thickness = .65;
        foreach ([$inset + $gap, $bottom - $gap - $thickness] as $y) {
            foreach ([$inset + $gap, $right - $gap - $length] as $x) {
                $lines[] = [$x, $y, $length, $thickness, 'accent'];
            }
        }
        foreach ([$inset + $gap, $right - $gap - $thickness] as $x) {
            foreach ([$inset + $gap, $bottom - $gap - $length] as $y) {
                $lines[] = [$x, $y, $thickness, $length, 'accent'];
            }
        }

        $center = $width / 2;
        $ornament = $inset + min(8, $side * .055);
        $rule = min(10, $width * .075);
        $lines[] = [$center - 2.25 - $rule, $ornament, $rule, .42, 'accent'];
        $lines[] = [$center + 2.25, $ornament, $rule, .42, 'accent'];
        $lines[] = [$center - .6, $ornament - .39, 1.2, 1.2, 'accent'];

        return $lines;
    }

    public static function isHexColor(mixed $value): bool
    {
        return is_string($value) && preg_match('/^#[a-f0-9]{6}$/iD', $value) === 1;
    }

    public static function resolveTheme(array $layout): array
    {
        $theme = $layout['theme'] ?? null;
        if (! is_array($theme)) {
            return self::LEGACY_THEME;
        }

        $requested = is_string($theme['palette'] ?? null) ? $theme['palette'] : null;
        $base = self::PALETTES[$requested] ?? self::PALETTES['midnight-navy'];

        $background = self::normalizeHex($theme['background'] ?? null, $base['background']);
        $accent = self::normalizeHex($theme['accent'] ?? null, $base['accent']);
        $frame = self::normalizeHex($theme['frame'] ?? null, $base['frame']);

        $matched = null;
        foreach (self::PALETTES as $key => $preset) {
            if ($background === $preset['background'] && $accent === $preset['accent'] && $frame === $preset['frame']) {
                $matched = $key;
                break;
            }
        }

        $text = $matched !== null
            ? self::PALETTES[$matched]['text']
            : (self::luminance($background) > 0.35 ? '#14233c' : '#faf6ee');

        return [
            'legacy' => false,
            'palette' => $matched ?? 'custom',
            'background' => $background,
            'accent' => $accent,
            'frame' => $frame,
            'text' => $text,
        ];
    }

    public static function defaultLayout(): array
    {
        $path = resource_path('certificates/certificate.json');
        $default = is_file($path) ? $path : __DIR__.'/../resources/templates/certificate.json';

        return File::json(config('certificate-generator.template', $default), JSON_THROW_ON_ERROR);
    }

    public static function blankLayout(float $width = 297.0, float $height = 210.0, ?string $background = null): array
    {
        $qrSide = 36.0;
        $qrX = max(0.0, round($width - $qrSide - 18.0, 1));
        $qrY = max(0.0, round($height - $qrSide - 18.0, 1));

        $layout = [
            'version' => 1,
            'unit' => 'mm',
            'page' => ['width' => $width, 'height' => $height],
            'frame' => $background === null,
            'theme' => [
                'palette' => 'midnight-navy',
                'background' => '#fffdf8',
                'accent' => '#a27d34',
                'frame' => '#14233c',
            ],
            'custom_variables' => [],
            'elements' => [
                [
                    'id' => 'verification',
                    'type' => 'qr',
                    'x' => $qrX,
                    'y' => $qrY,
                    'width' => $qrSide,
                    'height' => $qrSide,
                ],
            ],
        ];

        if ($background !== null) {
            $layout['background'] = $background;
        }

        return $layout;
    }

    /**
     * @return array{width: float, height: float}
     */
    public static function pageSizeForImage(int $imgWidth, int $imgHeight): array
    {
        if ($imgWidth <= 0 || $imgHeight <= 0) {
            return ['width' => 297.0, 'height' => 210.0];
        }

        if ($imgWidth >= $imgHeight) {
            return [
                'width' => 297.0,
                'height' => round(297.0 * ($imgHeight / $imgWidth), 1),
            ];
        }

        return [
            'width' => round(297.0 * ($imgWidth / $imgHeight), 1),
            'height' => 297.0,
        ];
    }

    public static function clampElementsToPage(array $layout): array
    {
        $pageW = (float) ($layout['page']['width'] ?? 297.0);
        $pageH = (float) ($layout['page']['height'] ?? 210.0);

        foreach ($layout['elements'] as &$element) {
            if (($element['type'] ?? null) === 'qr') {
                $side = max(30.0, min((float) $element['width'], $pageW, $pageH));
                $element['width'] = $side;
                $element['height'] = $side;
            } else {
                $element['width'] = min((float) $element['width'], $pageW);
                $element['height'] = min((float) $element['height'], $pageH);
            }

            $element['x'] = round(max(0.0, min((float) $element['x'], $pageW - (float) $element['width'])), 1);
            $element['y'] = round(max(0.0, min((float) $element['y'], $pageH - (float) $element['height'])), 1);
        }
        unset($element);

        return $layout;
    }

    public static function all(): Collection
    {
        return CertificateDesignRecord::query()
            ->orderByDesc('is_default')
            ->orderBy('category')
            ->orderBy('name')
            ->get();
    }

    public static function ensureDefaultRecord(): CertificateDesignRecord
    {
        $existing = CertificateDesignRecord::query()->orderBy('id')->first();
        if ($existing) {
            return $existing;
        }

        return CertificateDesignRecord::query()->create([
            'name' => 'Template Utama',
            'slug' => 'default',
            'category' => null,
            'is_default' => true,
            'number_format' => config('certificate-generator.number.format', 'CERT/{CAT}/{YYYY}/{SEQ:5}'),
            'layout' => self::defaultLayout(),
        ]);
    }

    public static function resolveForCategory(?string $category = null, mixed $design = null): ?CertificateDesignRecord
    {
        if ($design instanceof CertificateDesignRecord) {
            return $design;
        }

        if (is_int($design) || (is_string($design) && ctype_digit($design))) {
            $found = CertificateDesignRecord::query()->find((int) $design);
            if ($found) {
                return $found;
            }
        }

        if (is_string($design) && $design !== '') {
            $found = CertificateDesignRecord::query()->where('slug', $design)->first();
            if ($found) {
                return $found;
            }
        }

        if ($category !== null && $category !== '') {
            $catDefault = CertificateDesignRecord::query()
                ->where('category', $category)
                ->orderByDesc('is_default')
                ->orderBy('id')
                ->first();
            if ($catDefault) {
                return $catDefault;
            }
        }

        return CertificateDesignRecord::query()
            ->whereNull('category')
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->first()
            ?? CertificateDesignRecord::query()->orderByDesc('is_default')->orderBy('id')->first();
    }

    public static function current(): array
    {
        $record = self::resolveForCategory();

        return $record ? $record->layout : self::defaultLayout();
    }

    public static function save(array $layout): void
    {
        $record = self::resolveForCategory();
        if ($record) {
            $record->update(['layout' => $layout]);

            return;
        }

        CertificateDesignRecord::query()->create([
            'name' => 'Template Utama',
            'slug' => 'default',
            'category' => null,
            'is_default' => true,
            'number_format' => config('certificate-generator.number.format', 'CERT/{CAT}/{YYYY}/{SEQ:5}'),
            'layout' => $layout,
        ]);
    }

    public static function createTemplate(array $attributes): CertificateDesignRecord
    {
        $name = trim((string) ($attributes['name'] ?? 'Template Baru'));
        $category = ($attributes['category'] ?? null) ?: null;
        if ($category === 'general') {
            $category = null;
        }

        $baseSlug = Str::slug($name) ?: 'template';
        $slug = $baseSlug;
        $i = 2;
        while (CertificateDesignRecord::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$i;
            $i++;
        }

        $isDefault = (bool) ($attributes['is_default'] ?? false);

        return DB::transaction(function () use ($name, $slug, $category, $isDefault, $attributes): CertificateDesignRecord {
            if ($isDefault) {
                self::clearDefaultForCategory($category);
            }

            return CertificateDesignRecord::query()->create([
                'name' => $name,
                'slug' => $slug,
                'category' => $category,
                'is_default' => $isDefault,
                'number_format' => ($attributes['number_format'] ?? null) ?: config('certificate-generator.number.format'),
                'layout' => $attributes['layout'] ?? self::defaultLayout(),
            ]);
        });
    }

    public static function updateTemplate(CertificateDesignRecord $design, array $attributes): CertificateDesignRecord
    {
        return DB::transaction(function () use ($design, $attributes): CertificateDesignRecord {
            $category = array_key_exists('category', $attributes)
                ? (($attributes['category'] ?: null) === 'general' ? null : ($attributes['category'] ?: null))
                : $design->category;

            $isDefault = array_key_exists('is_default', $attributes)
                ? (bool) $attributes['is_default']
                : $design->is_default;

            if ($isDefault) {
                self::clearDefaultForCategory($category, $design->id);
            }

            $design->update([
                'name' => $attributes['name'] ?? $design->name,
                'category' => $category,
                'is_default' => $isDefault,
                'number_format' => array_key_exists('number_format', $attributes)
                    ? ($attributes['number_format'] ?: null)
                    : $design->number_format,
                'layout' => $attributes['layout'] ?? $design->layout,
            ]);

            return $design->fresh();
        });
    }

    private static function clearDefaultForCategory(?string $category, ?int $exceptId = null): void
    {
        $query = CertificateDesignRecord::query();
        if ($category === null) {
            $query->whereNull('category');
        } else {
            $query->where('category', $category);
        }

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        $query->update(['is_default' => false]);
    }

    public static function checkGeometry(array $layout): void
    {
        if (($layout['version'] ?? null) !== 1 || ($layout['unit'] ?? null) !== 'mm') {
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

    /**
     * @return array{int, int}
     */
    public static function checkBackground(string $uri): array
    {
        return self::imageSize($uri);
    }

    public static function imageSizes(array $layout): array
    {
        $images = [];
        if (! empty($layout['background'])) {
            $images['background'] = $layout['background'];
        }
        foreach ($layout['elements'] as $index => $element) {
            if ($element['type'] === 'image') {
                $images[$index] = $element['image'] ?? '';
            }
        }

        // Bound combined data URI payload (~10 MiB) to allow lossless 5 MiB background + logos/signatures.
        if (array_sum(array_map('strlen', $images)) > 10 * 1024 * 1024) {
            throw new UnexpectedValueException('Total gambar dalam satu desain terlalu besar. Kompres gambar atau hapus salah satu gambar.');
        }

        return array_map(self::imageSize(...), $images);
    }

    private static function imageSize(string $uri): array
    {
        $maxBytes = (int) config('certificate-generator.upload.max_bytes', 5 * 1024 * 1024);
        $maxMegapixels = (int) config('certificate-generator.upload.max_megapixels', 16_000_000);
        $maxBase64Len = (int) ceil($maxBytes * 4 / 3) + 64;

        $parts = explode(',', $uri, 2);
        $types = ['data:image/jpeg;base64' => IMAGETYPE_JPEG, 'data:image/png;base64' => IMAGETYPE_PNG];
        $bytes = isset($types[$parts[0]], $parts[1]) && strlen($parts[1]) <= $maxBase64Len
            ? base64_decode($parts[1], true)
            : false;
        $info = $bytes && strlen($bytes) <= $maxBytes ? @getimagesizefromstring($bytes) : false;
        $validSignature = $info && $info[2] === $types[$parts[0]] && $info[0] * $info[1] <= $maxMegapixels;
        $validTrailer = $bytes !== false && (
            ($info && $info[2] === IMAGETYPE_PNG && str_contains(substr($bytes, -32), 'IEND'))
            || ($info && $info[2] === IMAGETYPE_JPEG && str_contains(substr($bytes, -32), "\xFF\xD9"))
        );

        if (! $validSignature || ! $validTrailer) {
            throw new UnexpectedValueException('Gunakan JPG atau PNG yang dapat dibaca, maksimal 5 MiB dan 16 megapiksel.');
        }

        return [$info[0], $info[1]];
    }

    private static function normalizeHex(mixed $value, string $fallback): string
    {
        return self::isHexColor($value) ? strtolower($value) : $fallback;
    }

    private static function luminance(string $hex): float
    {
        $channels = array_map(static function (string $pair): float {
            $value = hexdec($pair) / 255;

            return $value <= 0.03928 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        }, str_split(ltrim($hex, '#'), 2));

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    }
}
