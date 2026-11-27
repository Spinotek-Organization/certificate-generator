<?php

namespace Spinotek\CertificateGenerator\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateDesignSnapshot extends Model
{
    protected $table = 'spinotek_certificate_design_snapshots';

    public $timestamps = false;

    protected $fillable = [
        'hash',
        'layout',
    ];

    protected $casts = [
        'layout' => 'array',
        'created_at' => 'datetime',
    ];

    public static function forLayout(array $layout): self
    {
        $encoded = json_encode($layout, JSON_THROW_ON_ERROR);
        $hash = hash('sha256', $encoded);

        return self::query()->firstOrCreate(
            ['hash' => $hash],
            ['layout' => $layout]
        );
    }
}
