<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_platform_id')->constrained()->onDelete('cascade');
            $table->text('caption')->nullable();
            $table->string('hook')->nullable();
            $table->string('youtube_title')->nullable();
            $table->text('youtube_description')->nullable();
            $table->json('hashtags')->nullable();
            $table->string('privacy_level')->default('public'); // public, private, unlisted, friends
            $table->boolean('made_for_kids')->default(false);
            $table->boolean('synthetic_content_disclosure')->default(false);
            $table->boolean('commercial_content_disclosure')->default(false);
            $table->integer('version')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_contents');
    }
};
