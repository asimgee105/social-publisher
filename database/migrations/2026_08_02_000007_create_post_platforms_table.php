<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_platforms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('social_account_id')->constrained()->onDelete('cascade');
            $table->string('platform_key'); // instagram, facebook, tiktok, youtube, linkedin
            $table->string('status')->default('draft'); // draft, queued, publishing, published, failed, cancelled, needs_reauth
            $table->string('platform_post_id')->nullable();
            $table->string('platform_post_url')->nullable();
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retries_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_platforms');
    }
};
