<?php

namespace Spinotek\CertificateGenerator\Support;

class BackgroundPresetRegistry
{
    /**
     * @var array<string, array{label: string, path: string}>
     */
    protected static array $runtimePresets = [];

    public static function register(string $key, string $label, string $path): void
    {
        if ($key !== '' && $label !== '' && $path !== '') {
            self::$runtimePresets[$key] = [
                'label' => $label,
                'path' => $path,
            ];
        }
    }

    public static function clearRuntime(): void
    {
        self::$runtimePresets = [];
    }

    /**
     * Metadata-only list so pages stay lightweight (no multi-MB base64 in HTML).
     *
     * @return list<array{key: string, label: string, path: string, mime: string, width: int, height: int}>
     */
    public static function all(): array
    {
        $configured = config('certificate-generator.backgrounds', []);
        $items = array_merge(is_array($configured) ? $configured : [], self::$runtimePresets);

        $maxBytes = (int) config('certificate-generator.upload.max_bytes', 5 * 1024 * 1024);
        $maxMegapixels = (int) config('certificate-generator.upload.max_megapixels', 16_000_000);

        $presets = [];
        foreach ($items as $key => $item) {
            if (! is_array($item) || empty($item['path']) || ! is_file($item['path'])) {
                continue;
            }

            $size = @filesize($item['path']);
            if ($size === false || $size > $maxBytes) {
                continue;
            }

            $info = @getimagesize($item['path']);
            if (! $info || ! in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
                continue;
            }

            if ($info[0] * $info[1] > $maxMegapixels) {
                continue;
            }

            $presets[] = [
                'key' => (string) $key,
                'label' => (string) ($item['label'] ?? ucfirst((string) $key)),
                'path' => $item['path'],
                'mime' => $info[2] === IMAGETYPE_PNG ? 'image/png' : 'image/jpeg',
                'width' => (int) $info[0],
                'height' => (int) $info[1],
            ];
        }

        return $presets;
    }

    /**
     * @return array{key: string, label: string, path: string, mime: string, width: int, height: int, uri: string}|null
     */
    public static function find(string $key): ?array
    {
        foreach (self::all() as $preset) {
            if ($preset['key'] === $key) {
                $bytes = @file_get_contents($preset['path']);
                if ($bytes === false) {
                    return null;
                }
                $preset['uri'] = 'data:'.$preset['mime'].';base64,'.base64_encode($bytes);

                return $preset;
            }
        }

        return null;
    }
}
