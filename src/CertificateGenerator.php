<?php

namespace Spinotek\CertificateGenerator;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Spinotek\CertificateGenerator\Models\CertificateDesignSnapshot;
use Spinotek\CertificateGenerator\Support\CertificateNumberGenerator;
use Spinotek\CertificateGenerator\Support\VariableRegistry;

class CertificateGenerator
{
    public static function registerVariables(array $variables): void
    {
        VariableRegistry::register($variables);
    }

    public function issue(array $data): Certificate
    {
        $data['issuer'] ??= config('app.name');
        $data['category'] ??= 'general';
        if (($data['issued_date'] ?? null) instanceof \DateTimeInterface) {
            $data['issued_date'] = $data['issued_date']->format('Y-m-d');
        }

        $fields = Validator::make($data, [
            'participant_name' => 'required|string|max:255',
            'activity_title' => 'required|string|max:255',
            'issued_date' => 'required|string|date_format:Y-m-d',
            'issuer' => 'required|string|max:255',
            'category' => 'required|string|max:64',
            'certificate_number' => 'nullable|string|max:120|unique:spinotek_certificates,certificate_number',
            'number_format' => 'nullable|string|max:120',
            'user_id' => 'nullable|integer',
            'variables' => 'nullable|array',
        ])->validate();

        $certifiable = $data['certifiable'] ?? null;
        if ($certifiable !== null && ! $certifiable instanceof Model) {
            throw new InvalidArgumentException('Parameter certifiable harus berupa instance Eloquent Model.');
        }

        $designRecord = CertificateDesign::resolveForCategory(
            $fields['category'],
            $data['design'] ?? null
        );
        $layout = $designRecord?->layout ?? CertificateDesign::defaultLayout();
        CertificateDesign::checkGeometry($layout);

        return DB::transaction(function () use ($fields, $certifiable, $designRecord, $layout, $data): Certificate {
            $code = (string) Str::uuid();
            $numberFormat = $fields['number_format']
                ?? $designRecord?->number_format
                ?? config('certificate-generator.number.format');

            $certificateNumber = ! empty($fields['certificate_number'])
                ? trim($fields['certificate_number'])
                : CertificateNumberGenerator::generate(
                    category: $fields['category'],
                    format: $numberFormat,
                    issuedDate: $fields['issued_date'],
                    context: $data['number_context'] ?? []
                );

            $snapshot = CertificateDesignSnapshot::forLayout($layout);

            $certificate = Certificate::query()->create([
                'code' => $code,
                'certificate_number' => $certificateNumber,
                'design_id' => $designRecord?->id,
                'design_snapshot_id' => $snapshot->id,
                'category' => $fields['category'],
                'certifiable_type' => $certifiable?->getMorphClass(),
                'certifiable_id' => $certifiable?->getKey(),
                'user_id' => $fields['user_id'] ?? null,
                'participant_name' => $fields['participant_name'],
                'activity_title' => $fields['activity_title'],
                'issued_date' => $fields['issued_date'],
                'issuer' => $fields['issuer'],
                'status' => Certificate::STATUS_VALID,
                'variables' => $fields['variables'] ?? [],
            ]);

            if (config('certificate-generator.storage.driver') === 'disk') {
                $certificate->renderPdf();
            }

            return $certificate;
        });
    }

    /**
     * Issue once or return existing certificate for a certifiable product and optional user.
     */
    public function issueOrGet(Model $certifiable, int|string|null $userId, array $data): Certificate
    {
        return DB::transaction(function () use ($certifiable, $userId, $data): Certificate {
            $query = Certificate::query()
                ->where('certifiable_type', $certifiable->getMorphClass())
                ->where('certifiable_id', $certifiable->getKey())
                ->lockForUpdate();

            if ($userId !== null) {
                $query->where('user_id', $userId);
            }

            $existing = $query->latest('created_at')->first();
            if ($existing) {
                return $existing;
            }

            $data['certifiable'] = $certifiable;
            if ($userId !== null && ! array_key_exists('user_id', $data)) {
                $data['user_id'] = $userId;
            }

            return $this->issue($data);
        }, attempts: 3);
    }

    public function reissue(Certificate $certificate, array $overrides = []): Certificate
    {
        return DB::transaction(function () use ($certificate, $overrides): Certificate {
            if ($certificate->pdf_path && $certificate->pdf_disk) {
                Storage::disk($certificate->pdf_disk)->delete($certificate->pdf_path);
            }

            $designRecord = isset($overrides['design'])
                ? CertificateDesign::resolveForCategory($certificate->category, $overrides['design'])
                : ($certificate->design ?? CertificateDesign::resolveForCategory($certificate->category));

            $layout = $designRecord?->layout ?? $certificate->snapshot?->layout ?? CertificateDesign::defaultLayout();
            $snapshot = CertificateDesignSnapshot::forLayout($layout);

            $certificate->fill([
                'design_id' => $designRecord?->id,
                'design_snapshot_id' => $snapshot->id,
                'participant_name' => $overrides['participant_name'] ?? $certificate->participant_name,
                'activity_title' => $overrides['activity_title'] ?? $certificate->activity_title,
                'issued_date' => $overrides['issued_date'] ?? ($certificate->issued_date?->format('Y-m-d') ?? today()->toDateString()),
                'issuer' => $overrides['issuer'] ?? $certificate->issuer,
                'certificate_number' => $overrides['certificate_number'] ?? $certificate->certificate_number,
                'variables' => array_key_exists('variables', $overrides)
                    ? $overrides['variables']
                    : $certificate->variables,
                'pdf_disk' => null,
                'pdf_path' => null,
            ]);
            $certificate->save();

            if (config('certificate-generator.storage.driver') === 'disk') {
                $certificate->renderPdf();
            }

            return $certificate->fresh();
        });
    }

    public function revoke(Certificate $certificate, ?string $reason = null): Certificate
    {
        if ($certificate->pdf_path && $certificate->pdf_disk) {
            Storage::disk($certificate->pdf_disk)->delete($certificate->pdf_path);
        }

        $certificate->forceFill([
            'status' => Certificate::STATUS_REVOKED,
            'revoked_at' => now(),
            'revoked_reason' => $reason,
            'pdf_disk' => null,
            'pdf_path' => null,
        ])->save();

        return $certificate->fresh();
    }

    public function restore(Certificate $certificate): Certificate
    {
        if ($certificate->pdf_path && $certificate->pdf_disk) {
            Storage::disk($certificate->pdf_disk)->delete($certificate->pdf_path);
        }

        $certificate->forceFill([
            'status' => Certificate::STATUS_VALID,
            'revoked_at' => null,
            'revoked_reason' => null,
            'pdf_disk' => null,
            'pdf_path' => null,
        ])->save();

        return $certificate->fresh();
    }

    public function verificationUrl(string $code): string
    {
        $base = rtrim((string) config('app.url'), '/');
        if (! filter_var($base, FILTER_VALIDATE_URL) || ! in_array(parse_url($base, PHP_URL_SCHEME), ['http', 'https'], true)
            || array_intersect(['user', 'pass', 'query', 'fragment'], array_keys(parse_url($base)))) {
            throw new InvalidArgumentException('APP_URL harus berupa URL dasar HTTP atau HTTPS aplikasi penerbit.');
        }

        return $base.route('spinotek.certificates.verify', ['code' => $code], false);
    }

    public function downloadUrl(string $code, bool $signed = false): string
    {
        return $signed
            ? URL::signedRoute('spinotek.certificates.download', ['code' => $code])
            : route('spinotek.certificates.download', ['code' => $code]);
    }
}
