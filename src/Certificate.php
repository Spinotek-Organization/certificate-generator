<?php

namespace Spinotek\CertificateGenerator;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

class Certificate extends Model
{
    protected $table = 'spinotek_certificates';
    protected $primaryKey = 'code';
    protected $keyType = 'string';
    protected $hidden = ['pdf_base64'];
    public $incrementing = false;
    public $timestamps = false;

    public function download(): Response
    {
        return response(base64_decode($this->pdf_base64), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="sertifikat-'.$this->code.'.pdf"',
        ]);
    }
}
