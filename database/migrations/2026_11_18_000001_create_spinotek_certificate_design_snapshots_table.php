<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spinotek_certificate_design_snapshots', function (Blueprint $table) {
            $table->id();
            $table->char('hash', 64)->unique();
            $table->longText('layout');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spinotek_certificate_design_snapshots');
    }
};
