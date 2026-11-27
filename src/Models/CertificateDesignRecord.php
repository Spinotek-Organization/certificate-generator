<?php

namespace Spinotek\CertificateGenerator\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spinotek\CertificateGenerator\Certificate;

class CertificateDesignRecord extends Model
{
    protected $table = 'spinotek_certificate_designs';

    protected $fillable = [
        'name',
        'slug',
        'category',
        'is_default',
        'number_format',
        'layout',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'layout' => 'array',
    ];

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class, 'design_id');
    }

    public function categoryLabel(): string
    {
        $categories = config('certificate-generator.categories', []);

        if ($this->category === null || $this->category === '') {
            return $categories['general'] ?? 'Umum';
        }

        return $categories[$this->category] ?? ucfirst($this->category);
    }
}
