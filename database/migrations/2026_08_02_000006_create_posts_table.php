<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('media_asset_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title')->nullable();
            $table->text('base_content')->nullable();
            $table->string('status')->default('draft'); // draft, scheduled, publishing, published, failed, partial_success, cancelled
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('timezone')->default('Asia/Karachi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
