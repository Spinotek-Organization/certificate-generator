<?php

namespace Spinotek\CertificateGenerator\Support;

use Closure;
use Illuminate\Support\Str;
use Spinotek\CertificateGenerator\Certificate;

class CertificateNumberGenerator
{
    protected static ?Closure $customResolver = null;

    public static function resolveUsing(?Closure $resolver): void
    {
        self::$customResolver = $resolver;
    }

    public static function generate(
        string $category = 'general',
        ?string $format = null,
        ?string $issuedDate = null,
        array $context = []
    ): string {
        if (self::$customResolver !== null) {
            $resolved = (self::$customResolver)($category, $format, $issuedDate, $context);
            if (is_string($resolved) && $resolved !== '') {
                return $resolved;
            }
        }

        $format = trim((string) ($format ?: config('certificate-generator.number.format', 'CERT/{CAT}/{YYYY}/{SEQ:5}')));
        if ($format === '') {
            $format = 'CERT/{CAT}/{YYYY}/{SEQ:5}';
        }

        $date = $issuedDate ? strtotime($issuedDate) : time();
        if ($date === false) {
            $date = time();
        }

        $categoryCodes = config('certificate-generator.number.category_codes', []);
        $catCode = $categoryCodes[$category] ?? strtoupper(Str::slug($category, ''));
        $prefix = (string) ($context['prefix'] ?? config('certificate-generator.number.prefix', 'CERT'));

        $baseReplacements = [
            '{PREFIX}' => $prefix,
            '{CATEGORY}' => strtoupper(Str::slug($category, '-')),
            '{CAT}' => $catCode !== '' ? $catCode : 'GEN',
            '{YYYY}' => date('Y', $date),
            '{YY}' => date('y', $date),
            '{MM}' => date('m', $date),
            '{DD}' => date('d', $date),
        ];

        $pattern = strtr($format, $baseReplacements);

        // Support {RAND:N}
        $pattern = preg_replace_callback('/\{RAND(?::(\d+))?\}/', static function (array $m): string {
            $len = max(2, min(12, (int) ($m[1] ?? 4)));
            $min = 10 ** ($len - 1);
            $max = (10 ** $len) - 1;

            return (string) random_int($min, $max);
        }, $pattern) ?? $pattern;

        // Support {UUID_SHORT}
        if (str_contains($pattern, '{UUID_SHORT}')) {
            $pattern = str_replace('{UUID_SHORT}', strtoupper(substr((string) Str::uuid(), 0, 8)), $pattern);
        }

        // Support {SEQ} or {SEQ:N}
        if (preg_match('/\{SEQ(?::(\d+))?\}/', $pattern, $seqMatch)) {
            $padLength = max(1, min(10, (int) ($seqMatch[1] ?? 5)));
            $token = $seqMatch[0];

            // Count existing certificates in the same category and year as starting point,
            // then increment until a unique certificate_number is found.
            $year = date('Y', $date);
            $sequence = Certificate::query()
                ->where('category', $category)
                ->whereYear('issued_date', $year)
                ->count() + 1;

            do {
                $formattedSeq = str_pad((string) $sequence, $padLength, '0', STR_PAD_LEFT);
                $candidate = str_replace($token, $formattedSeq, $pattern);
                $exists = Certificate::query()->where('certificate_number', $candidate)->exists();
                $sequence++;
            } while ($exists);

            return $candidate;
        }

        // Fallback uniqueness if format didn't include {SEQ}
        $candidate = $pattern;
        $suffix = 1;
        while (Certificate::query()->where('certificate_number', $candidate)->exists()) {
            $candidate = $pattern.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }
}
