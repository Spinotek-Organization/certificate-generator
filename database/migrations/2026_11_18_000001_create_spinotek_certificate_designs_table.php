<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spinotek_certificate_designs', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->longText('layout');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spinotek_certificate_designs');
    }
};
