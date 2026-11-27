<?php

namespace Spinotek\CertificateGenerator\Support;

class VariableRegistry
{
    /**
     * @var array<string, string>
     */
    protected static array $runtimeVariables = [];

    /**
     * Register extra variables available globally in Certificate Creator & renderer.
     *
     * @param array<string, string> $variables [key => label]
     */
    public static function register(array $variables): void
    {
        foreach ($variables as $key => $label) {
            if (is_string($key) && $key !== '' && is_string($label) && $label !== '') {
                self::$runtimeVariables[$key] = $label;
            }
        }
    }

    public static function clearRuntime(): void
    {
        self::$runtimeVariables = [];
    }

    /**
     * @return array<string, string>
     */
    public static function all(?array $layout = null): array
    {
        $defaults = [
            'participant_name' => 'Nama Peserta',
            'activity_title' => 'Judul Kegiatan / Kelas',
            'certificate_number' => 'Nomor Sertifikat',
            'category' => 'Kategori',
            'issuer' => 'Penerbit',
            'issued_date' => 'Tanggal Terbit',
            'status' => 'Status',
        ];

        $configured = config('certificate-generator.variables', []);
        $custom = [];
        foreach (($layout['custom_variables'] ?? []) as $item) {
            if (is_array($item) && ! empty($item['key']) && ! empty($item['label'])) {
                $custom[(string) $item['key']] = (string) $item['label'];
            }
        }

        return array_merge($defaults, is_array($configured) ? $configured : [], self::$runtimeVariables, $custom);
    }

    /**
     * @return array<string, string>
     */
    public static function sampleValues(?array $layout = null): array
    {
        $samples = [
            'participant_name' => 'Rani Putri',
            'activity_title' => 'Lokakarya Desain Informasi',
            'certificate_number' => 'CERT/EVT/2026/00001',
            'category' => 'Event',
            'issuer' => config('app.name', 'Spinotek'),
            'issued_date' => today()->toDateString(),
            'status' => 'Valid',
        ];

        foreach (self::all($layout) as $key => $label) {
            if (! array_key_exists($key, $samples)) {
                $samples[$key] = '['.$label.']';
            }
        }

        foreach (($layout['custom_variables'] ?? []) as $item) {
            if (is_array($item) && ! empty($item['key']) && isset($item['sample']) && $item['sample'] !== '') {
                $samples[(string) $item['key']] = (string) $item['sample'];
            }
        }

        return $samples;
    }
}
