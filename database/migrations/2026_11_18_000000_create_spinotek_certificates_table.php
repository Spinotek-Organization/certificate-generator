<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spinotek_certificates', function (Blueprint $table) {
            $table->uuid('code')->primary();
            $table->string('participant_name');
            $table->string('activity_title');
            $table->date('issued_date');
            $table->string('issuer');
            $table->string('status');
            $table->longText('pdf_base64');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spinotek_certificates');
    }
};
