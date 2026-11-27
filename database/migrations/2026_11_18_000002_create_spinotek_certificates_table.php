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
            $table->string('certificate_number')->unique();
            $table->foreignId('design_id')->nullable()->constrained('spinotek_certificate_designs')->nullOnDelete();
            $table->foreignId('design_snapshot_id')->nullable()->constrained('spinotek_certificate_design_snapshots')->nullOnDelete();
            $table->string('category')->default('general')->index();
            $table->nullableMorphs('certifiable');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('participant_name');
            $table->string('activity_title');
            $table->date('issued_date');
            $table->string('issuer');
            $table->string('status')->default('Valid')->index();
            $table->json('variables')->nullable();
            $table->string('pdf_disk')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('revoked_reason')->nullable();
            $table->timestamps();

            $table->index(['category', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spinotek_certificates');
    }
};
