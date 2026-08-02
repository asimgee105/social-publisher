<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_asset_id')->constrained()->onDelete('cascade');
            $table->string('aspect_ratio'); // 9:16, 1:1, 16:9
            $table->string('path');
            $table->string('status')->default('ready'); // processing, ready, failed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_variants');
    }
};
