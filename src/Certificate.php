<?php

namespace Spinotek\CertificateGenerator;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Spinotek\CertificateGenerator\Models\CertificateDesignRecord;
use Spinotek\CertificateGenerator\Models\CertificateDesignSnapshot;

class Certificate extends Model
{
    public const STATUS_VALID = 'Valid';
    public const STATUS_REVOKED = 'Dicabut';

    protected $table = 'spinotek_certificates';
    protected $primaryKey = 'code';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'certificate_number',
        'design_id',
        'design_snapshot_id',
        'category',
        'certifiable_type',
        'certifiable_id',
        'user_id',
        'participant_name',
        'activity_title',
        'issued_date',
        'issuer',
        'status',
        'variables',
        'pdf_disk',
        'pdf_path',
        'revoked_at',
        'revoked_reason',
    ];

    protected $casts = [
        'issued_date' => 'date:Y-m-d',
        'variables' => 'array',
        'revoked_at' => 'datetime',
    ];

    public function design(): BelongsTo
    {
        return $this->belongsTo(CertificateDesignRecord::class, 'design_id');
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(CertificateDesignSnapshot::class, 'design_snapshot_id');
    }

    public function certifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeForUser(Builder $query, int|string $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeValid(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_VALID);
    }

    public function isValid(): bool
    {
        return $this->status === self::STATUS_VALID;
    }

    public function isRevoked(): bool
    {
        return $this->status === self::STATUS_REVOKED;
    }

    public function categoryLabel(): string
    {
        $categories = config('certificate-generator.categories', []);

        return $categories[$this->category] ?? ucfirst((string) $this->category);
    }

    public function templateFields(): array
    {
        $issuedDate = $this->issued_date instanceof DateTimeInterface
            ? $this->issued_date->format('Y-m-d')
            : (string) $this->issued_date;

        $base = [
            'participant_name' => $this->participant_name,
            'activity_title' => $this->activity_title,
            'certificate_number' => $this->certificate_number,
            'category' => $this->categoryLabel(),
            'issuer' => $this->issuer,
            'issued_date' => $issuedDate,
            'status' => $this->status,
        ];

        return array_merge($this->variables ?? [], $base);
    }

    public function renderPdf(): string
    {
        if ($this->pdf_path && $this->pdf_disk && Storage::disk($this->pdf_disk)->exists($this->pdf_path)) {
            return Storage::disk($this->pdf_disk)->get($this->pdf_path);
        }

        $layout = $this->snapshot?->layout
            ?? $this->design?->layout
            ?? CertificateDesign::current();

        $url = app(CertificateGenerator::class)->verificationUrl($this->code);
        $pdf = (new CertificatePdf)->render($this->templateFields(), $url, $layout);

        if (config('certificate-generator.storage.driver') === 'disk') {
            $disk = config('certificate-generator.storage.disk', 'local');
            $basePath = trim((string) config('certificate-generator.storage.path', 'certificates'), '/');
            $path = $basePath.'/'.$this->category.'/'.$this->code.'.pdf';
            Storage::disk($disk)->put($path, $pdf);
            $this->forceFill(['pdf_disk' => $disk, 'pdf_path' => $path])->saveQuietly();
        }

        return $pdf;
    }

    public function download(bool $inline = false): Response
    {
        $pdf = $this->renderPdf();
        $safeNumber = Str::slug($this->certificate_number ?: $this->code, '-');
        $disposition = $inline ? 'inline' : 'attachment';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="sertifikat-'.$safeNumber.'.pdf"',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }

    public function inline(): Response
    {
        return $this->download(inline: true);
    }

    public function downloadUrl(bool $signed = false, ?DateTimeInterface $expiration = null, bool $inline = false): string
    {
        $params = ['code' => $this->code];
        if ($inline) {
            $params['inline'] = 1;
        }

        if ($signed) {
            return $expiration
                ? URL::temporarySignedRoute('spinotek.certificates.download', $expiration, $params)
                : URL::signedRoute('spinotek.certificates.download', $params);
        }

        return route('spinotek.certificates.download', $params);
    }

    public function verificationUrl(): string
    {
        return app(CertificateGenerator::class)->verificationUrl($this->code);
    }
}
