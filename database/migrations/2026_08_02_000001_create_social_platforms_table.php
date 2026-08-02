<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_platforms', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // instagram, facebook, tiktok, youtube, linkedin
            $table->string('name');
            $table->string('icon')->nullable();
            $table->boolean('supports_scheduling')->default(true);
            $table->boolean('supports_direct_publish')->default(true);
            $table->boolean('supports_draft_upload')->default(false);
            $table->boolean('supports_video')->default(true);
            $table->boolean('supports_image')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_platforms');
    }
};
