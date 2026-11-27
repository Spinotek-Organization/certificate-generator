<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spinotek_certificate_designs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->nullable()->index();
            $table->boolean('is_default')->default(false)->index();
            $table->string('number_format')->nullable();
            $table->longText('layout');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spinotek_certificate_designs');
    }
};
